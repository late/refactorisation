<?php

class TemplateManager
{
    private $_quoteFromRepository;
    private $usefulObject;
    private $destination;
    private $destinationOfQuote;

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $this->init($data);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);


        return $replaced;
    }

    /***
    **** Cette fonction a pour but d'initialiser les attributs utilises par la classe
    **** Il ne doit pas y avoir de remplacement fait ici
    ***/
    private function init(array $data)
    {
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $this->_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $this->usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $this->destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            if(strpos($text, '[quote:destination_link]') !== false){
                $this->destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }
        }
    }

    /***
    **** Cette fonction cherche des tags définis dans $data dans la chaine de caracteres $text
    **** Si elle en trouve un, elle le remplace et retourne le texte 
    ***/
    private function computeText($text, array $data)
    { 
        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary     = strpos($text, '[quote:summary]');
        $containsDestinationName = strpos($text, '[quote:destination_name]');
        
        if ($containsSummaryHtml !== false) {
            $text = str_replace('[quote:summary_html]', Quote::renderHtml($this->_quoteFromRepository), $text);
        }
        if ($containsSummary !== false) {
            $text = str_replace('[quote:summary]', Quote::renderText($this->_quoteFromRepository), $text);
        }
        if ($containsDestinationName !== false) {
            $text = str_replace('[quote:destination_name]',$this->destinationOfQuote->countryName,$text);
        }

        
        if (isset($this->destination)) {
            $text = str_replace('[quote:destination_link]', $this->usefulObject->url . '/' . $this->destination->countryName . '/quote/' . $this->_quoteFromRepository->id, $text);
        } else {
            $text = str_replace('[quote:destination_link]', '', $text);
        }

        if (strpos($text, '[user:first_name]') !== false) {
            $_user = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : ApplicationContext::getInstance()->getCurrentUser();
            $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }
}

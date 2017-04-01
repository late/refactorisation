<?php

class TemplateManager
{
    private $_quoteFromRepository;
    private $usefulObject;
    private $destination;
    private $destinationOfQuote;

    private $replacements = [
                             ['needle' => '[quote:destination_name]', 'method' => 'setDestinationName'],
                             ['needle' => '[quote:summary_html]', 'method' => 'setSummaryHtml'],
                             ['needle' => '[quote:summary]', 'method' => 'setSummary'],
                             ['needle' => '[user:first_name]', 'method' => 'setFirstName'],
                             ['needle' => '[quote:destination_link]', 'method' => 'setDestinationLink', 'ifNotFound' => 'setDestinationLinkDefault'] 
                            ];

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
            $this->destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        }
    }

    /***
    **** Cette fonction cherche des tags définis dans $data dans la chaine de caracteres $text
    **** Si elle en trouve un, elle le remplace et retourne le texte 
    ***/
    private function computeText($text, array $data)
    {
        // On boucle sur les tags qu'on recherche
        for ($i = 0; $i < count($this->replacements); $i++) {
            // On check si le tag est dans le texte
            if (strpos($text, $this->replacements[$i]['needle'])) {
                //Si oui, on appelle la methode de remplacement
                $this->{$this->replacements[$i]['method']}($text, $this->replacements[$i]['needle']);
            } else if (array_key_exists('ifNotFound', $this->replacements[$i])) {
                //Si non, on appelle la methode par defaut, si elle est definie
                $this->{$this->replacements[$i]['ifNotFound']}($text);
            }
        }
        return $text;
    }

    private function setSummaryHtml(&$text, $needle)
    {
         $text = str_replace($needle, Quote::renderHtml($this->_quoteFromRepository), $text);
    }

    private function setSummary(&$text, $needle)
    {
         $text = str_replace($needle, Quote::renderText($this->_quoteFromRepository), $text);
    }

    private function setDestinationName(&$text, $needle)
    {
        $text = str_replace($needle, $this->destinationOfQuote->countryName,$text);
    }

    private function setFirstName(&$text, $needle)
    {
         $_user = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : ApplicationContext::getInstance()->getCurrentUser();
         $text = str_replace($needle, ucfirst(mb_strtolower($_user->firstname)), $text);
    }
    
    private function setDestinationLink(&$text, $needle)
    {
         $text = str_replace('[quote:destination_link]', $this->usefulObject->url . '/' . $this->destination->countryName . '/quote/' . $this->_quoteFromRepository->id, $text);
    }

    private function setDestinationLinkDefault(&$text)
    {
         $text = str_replace('[quote:destination_link]', '', $text);
    }
}

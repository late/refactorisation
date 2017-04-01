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

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary     = strpos($text, '[quote:summary]');

        if ($containsSummaryHtml !== false) {
            $text = str_replace('[quote:summary_html]', Quote::renderHtml($this->_quoteFromRepository), $text);
        }
        if ($containsSummary !== false) {
            $text = str_replace('[quote:summary]', Quote::renderText($this->_quoteFromRepository), $text);
        }


        (strpos($text, '[quote:destination_name]') !== false) and $text = str_replace('[quote:destination_name]',$this->destinationOfQuote->countryName,$text);


        if (isset($this->destination))
            $text = str_replace('[quote:destination_link]', $this->usefulObject->url . '/' . $this->destination->countryName . '/quote/' . $this->_quoteFromRepository->id, $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }
}

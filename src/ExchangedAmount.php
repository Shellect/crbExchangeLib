<?php /** @noinspection HttpUrlsUsage */

namespace PrivateCoolLib;

use XMLReader;

class ExchangedAmount
{
    private string $url = 'http://www.cbr.ru/scripts/XML_daily.asp';

    public function __construct(private readonly Currency $from, private readonly Currency $to, private readonly float $amount)
    {
    }


    /**
     * @return float
     */
    public function toDecimal(): float
    {
        if($this->amount <= 0){
            trigger_error("Amount must be greater than 0", E_USER_ERROR);
        }
        if($this->from === $this->to){
            return $this->amount;
        }
        $reader = XMLReader::XML($this->getResponse());
        $currencies = $this->handleResponse($reader);
        $reader->close();
        return $this->exchange($currencies);
    }

    /**
     * @return string
     */
    private function getResponse(): string
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $error_message = curl_error($ch);
            trigger_error($error_message);
        }
        curl_close($ch);
        return $resp;
    }


    /**
     * @param XMLReader $reader
     * @return array
     */
    private function handleResponse(XMLReader $reader): array
    {
        $result = [];
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                switch ($reader->name) {
                    case 'Valute' :
                        $result[] = $this->handleResponse($reader);
                        break;
                    case 'CharCode':
                    case 'Nominal':
                        $result[$reader->name] = $reader->readInnerXml();
                        break;
                    case 'Value':
                        $result[$reader->name] = str_replace(',', '.', $reader->readInnerXml());
                        break;
                }
            } else if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'Valute') {
                break;
            }
        }
        return $result;
    }

    /**
     * @param array $currencies
     * @return float
     */
    private function exchange(array $currencies): float
    {
        $from = 1;
        $to = 1;
        foreach ($currencies as $currency) {
            if ($currency['CharCode'] === $this->from->name) {
                $from = $currency['Value'] / $currency['Nominal'];
            } elseif ($currency['CharCode'] === $this->to->name) {
                $to = $currency['Value'] / $currency['Nominal'];
            }
        }
        return round($from / $to * $this->amount, 2);
    }
}
<?php
/**
 * Doviz
 *
 * @author Ahmet Özfen <ahmet.ozfen@gmail.com>
 *
 */

class Doviz
{
    public $type = "";
    public $key = null;

    public function getExchange(){
        if($this->type === "doviz"){
            if ($this->key === null){
                return $this->getDoviz();
            } else{
                return $this->getBankVariables("https://kur.doviz.com/serbest-piyasa/" . $this->key);
            }
        }

        if($this->type === "altin"){
            if ($this->key === null){
                return $this->getGolds();
            } else{
                return $this->getBankVariables("https://altin.doviz.com/" . $this->key);
            }
        }
    }

    private function getDoviz()
    {
        $url = "https://kur.doviz.com/";
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $body =  curl_exec($ch);
        curl_close($ch);
        preg_match_all('#<tbody>(.*?)</tbody>#ims', $body, $getResult);
        $rows = explode("<tr>", $getResult[0][0]);
        array_shift($rows);

        $data = [];
        foreach ($rows as $row){
            preg_match_all('#<a href="(.*?)">#ims', $row, $getDetailUrl);
            $detailUrl = "https:" . $getDetailUrl[1][0];
            $urlExp = explode("/", $detailUrl);
            $key = $urlExp[count($urlExp) - 1];

            preg_match_all('#</span>(.*?)</a>#ims', $row, $getDisplayName);
            $displayName = $getDisplayName[1][0];

            $newRow = explode("<td>", $row);
            array_shift($newRow);
            preg_match_all('#data-socket-key="(.*?)"#ims', $newRow[0], $getSymbol);

            $symbol = $getSymbol[1][0];
            preg_match_all('#<td (.*?)</td>#ims', $newRow[0], $getExchange);
            $exchange_buy =  strip_tags($getExchange[0][0]);
            $exchange_sell =  strip_tags($getExchange[0][1]);

            preg_match_all('#<td (.*?)</td>#ims', $newRow[2], $getChange);
            $exchange_change =  strip_tags($getChange[0][0]);

            $data[] = [
                "name" => trim($displayName),
                "symbol" => $symbol,
                "key" => $key,
                "alis" => str_replace([","], ["."], $exchange_buy),
                "satis" => str_replace([","], ["."], $exchange_sell),
                "degisim" => str_replace([","], ["."], trim($exchange_change))
            ];
        }
        return $data;
    }

    private function getGolds()
    {
        $url = "https://altin.doviz.com/";
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $body =  curl_exec($ch);
        curl_close($ch);
        preg_match_all('#<table id="golds">(.*?)</table>#ims', $body, $getResult);
        $rows = explode("<tr>", $getResult[1][0]);
        array_shift($rows);
        array_shift($rows);
        $data = [];
        foreach ($rows as $key => $row){
            preg_match_all('#<a href="(.*?)">#ims', $row, $getDetailUrl);
            $detailUrl = "https:" . $getDetailUrl[1][0];
            $urlExp = explode("/", $detailUrl);
            $key = $urlExp[count($urlExp) - 1];

            preg_match_all('#</span>(.*?)</a>#ims', $row, $getDisplayName);
            $displayName = $getDisplayName[1][0];

            $newRow = explode("<td>", $row);
            array_shift($newRow);
            preg_match_all('#data-socket-key="(.*?)"#ims', $newRow[0], $getSymbol);


            $symbol = $getSymbol[1][0];
            preg_match_all('#<td (.*?)</td>#ims', $newRow[0], $getExchange);

            $exchange_buy =  strip_tags($getExchange[0][0]);
            $exchange_sell =  strip_tags($getExchange[0][1]);
            $exchange_change =  strip_tags($getExchange[0][2]);

            $data[] = [
                "name" => trim($displayName),
                "key" => $key,
                "alis" => str_replace([","], ["."], $exchange_buy),
                "satis" => str_replace([","], ["."], $exchange_sell),
                "degisim" => str_replace([","], ["."], trim($exchange_change))
            ];
        }
        return $data;
    }

    private function getBankVariables($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $body =  curl_exec($ch);
        curl_close($ch);
        preg_match_all('#<div class="table table-narrow">(.*?)</div>#ims', $body, $getResult);
        $rows = explode("<tr>", $getResult[1][0]);
        array_shift($rows);
        array_shift($rows);
        $rows2 = explode("<tr>", $getResult[1][1]);
        array_shift($rows2);
        array_shift($rows2);
        $allRows = array_merge($rows, $rows2);

        $data = [];
        foreach ($allRows as $row){
            preg_match_all('#">(.*?)</a>#ims', $row, $getBankName);
            preg_match_all('#<td class="text-bold">(.*?)</td>#ims', $row, $getFiats);

            $bankName = $getBankName[1][0];
            $alis = $getFiats[1][0];
            $satis = $getFiats[1][1];

             $data[] = [
                 "bank_name" => $bankName,
                 "alis" => $alis,
                 "satis" => $satis
             ];
        }
        return $data;
    }
}
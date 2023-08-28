<?php
class QBviaCurl{
    private  $url_origin = 'https://api.salescontrolcenter.com/';

    public function httpost_curl($url,$data){
        $url1 =$this->url_origin.$url;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $buyer_add_rsl = curl_exec($curl);

        curl_close($curl);
        return $buyer_add_rsl;

    }
}

?>

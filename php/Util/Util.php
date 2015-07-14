<?php   

class Util {

    /*
     * Função que valida o CNPJ (sem mascara).
     */
    public static function validaCnpj($cnpj){
        
        if(!preg_match("@[0-9]{14}@",$cnpj)) return false;
        
        $k1 = 5;
        $k2 = 6;
        $soma1 = 0;
        $soma2 = 0;
        for($i=0;$i<12;$i++){
            $soma1 += ($cnpj[$i]*$k1);
            $soma2 += ($cnpj[$i]*$k2);
            $k1--;
            $k2--;
            if($k1==1) $k1=9;
            if($k2==1) $k2=9;
        }
        $dv1 =  (($soma1%11<2)?0:(11-($soma1%11)));
        $soma2 += ($dv1*$k2);
        $dv2 =  (($soma2%11<2)?0:(11-($soma2%11)));
        return ( $cnpj[12]== $dv1 && $cnpj[13]== $dv2 );        
    }   
    
    public static function preparaHtml($content, $utf8 = true){
        // codifica os conteúdo para UTF-8 se necessário
        if ($utf8) {
            if(!Util::is_utf8($content)){
                $content = utf8_encode($content);
            }
        }
        $content = html_entity_decode($content,null,'UTF-8');
        $content = preg_replace('@\s+@',' ',$content);
        $content = preg_replace('@>\s*<@','><',$content);
        //caracter bizarro
        $content = preg_replace('@\xC2\xA0@','', $content);
        
        return $content; 
    }

	public static function is_utf8($text){
		return preg_match('%^(?:
		        [\x09\x0A\x0D\x20-\x7E]              # ASCII
		        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		        )*$%xs',
		   $text);
	}
 
}
?>

<?php
include_once('Spider.php');
include_once('Util/Util.php');

/**
 * @author Baltasar Santos <baltasarc.s@gmail.com>
 */
class SintegraEs {

	function searchByCnpj( $cnpj ) {

		if (!Util::validaCnpj($cnpj)) throw new Exception("CNPJ inválido!");

		$spider = new Spider();

		$params['url'] = 'http://www.sintegra.es.gov.br/resultado.php';
		$params['http_verb'] = 'POST';
		$params['referer'] = 'http://www.sintegra.es.gov.br';
		$params['params']['botao'] = 'Consultar';
		$params['params']['num_ie'] = '';
		$params['params']['num_cnpj'] = $cnpj;									

		$response = $spider->request( $params['url'], $params['http_verb'], $params['referer'], $params['params'] );
		
		try {

			$result = $this->step1Parse( $response );
		
			echo json_encode( $result, JSON_PRETTY_PRINT );

		} catch (Exception $e) {
			throw $e;
		}
	}

	function step1Parse( $result ){

		//die($result."\n"); exit;
		$result = Util::preparaHtml(str_replace("&nbsp;"," ",$result));				
		
		#CNPJ não existente
		$pattern = '@CNPJ\s*.*?\s*n.*?o\s*existente\s*em\s*nossa\s*base\s*de\s*dados!@i';
		$int = preg_match($pattern,$result,$matches);
		if (count($matches) > 0) throw new Exception("CNPJ não existente em nossa base de dados!");
		
		$pattern = '@CNPJ\s*n.*?o\s*cadastrado\s*em\s*nossa\s*Base\s*de\s*Dados@i';
		$int = preg_match($pattern,$result,$matches);

		if (count($matches) > 0) throw new Exception("CNPJ não cadastrado em nossa base de dados!");		
		
		try {
		
			$data['identificacao'] = $this->getIdentificacao($result);
			
			$data['endereco'] = $this->getEndereco($result);
			
			$data['info_complementares'] = $this->getComplemento($result);
			
			$data['consulta'] = $this->getConsulta($result);				
			
		} catch (Exception $e) {
			throw $e;
		}
		
		return $data;
	}

	function getIdentificacao($result) {

		$patterns = array(	
				'cnpj'		=> '@<td\b[^>]*?>\s*CNPJ:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@is',
				'ie'		=> '@<td\b[^>]*?>\s*Inscri.*?o\s*Estadual:</td><td\b[^>]*?>\s*(.*?)\s*</td>@is',
				'razao_social'	=> '@<td\b[^>]*?>\s*Raz.*?o\s*Social\s*:</td><td\b[^>]*?>\s*(.*?)\s*</td>@is'
				);

		foreach ($patterns as $c => $p) {

			$int = preg_match($p,$result,$matches);
		
			if(count($matches) == 0) throw new Exception("Spider Broken at $c");
			
			$data[$c] = trim($matches[1]);			
		}
		
		return $data;
		
	}
	
	function getEndereco($result) {
	
		$patterns = array(	
				'logradouro'	=> '@<td\b[^>]*?>\s*Logradouro:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'numero'	=> '@<td\b[^>]*?>\s*N.*?mero:</td><td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'complemento'	=> '@<td\b[^>]*?>\s*Complemento:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'bairro'	=> '@<td\b[^>]*?>\s*Bairro:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'municipio'	=> '@<td\b[^>]*?>\s*Munic.*?pio:</td><td\b[^>]*?>(.*?)\s*</td>@i',
				'uf'		=> '@<td\b[^>]*?>\s*UF:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'cep'		=> '@<td\b[^>]*?>\s*CEP:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'telefone'	=> '@<td\b[^>]*?>\s*Telefone:</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i'	
				);

		foreach ($patterns as $c => $p) {

			$int = preg_match($p,$result,$matches);

			if(count($matches) == 0) throw new Exception("Spider Broken at $c");

			$data[$c] = trim($matches[1]);
		}
		
		return $data;
		
	}
	
	function getComplemento($result) {

		$patterns = array(	
				'atividade'		=> '@<td\b[^>]*?>\s*Atividade\s*Econ.*?mica\s*:\s*</td><td\b[^>]*?>\s*(.*?)\s*</td>@is',
				'data_inicio'		=> '@<td\b[^>]*?>\s*Data\s*de\s*Inicio\s*de\s*Atividade\s*:\s*</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'situacao_vigente'	=> '@<td\b[^>]*?>\s*Situa.{1,16}o\s*Cadastral\s*Vigente\s*:\s*</td><td\b[^>]*?>\s*(.*?)\s*</td>@is',
				'data_situacao_vigente'	=> '@<td\b[^>]*?>\s*Data\s*desta\s*Situa.*?o\s*Cadastral\s*:\s*</td><td\b[^>]*?>\s*(.*?)\s*</td>@is',
				'regime_apuracao'	=> '@<td\b[^>]*?>\s*Regime\s*de\s*Apura.{1,16}\s*:\s*</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'emitente_nfe_desde'	=> '@<td\b[^>]*?>\s*Emitente\s*de\s*NFe\s*desde:\s*</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i',
				'obrigada_nfe'		=> '@<td\b[^>]*?>\s*Obrigada\s*a\s*NF-e\s*em:\s*</td>\s*<td\b[^>]*?>\s*(.*?)\s*</td>@i'							
				);
							
		
		foreach ($patterns as $c => $p) {

			$int = preg_match($p,$result,$matches);

			if (count($matches) == 0) throw new Exception("Spider Broken at $c");

			$data[$c] = ( $c == 'atividade' )? utf8_encode((trim($matches[1]))) :trim($matches[1]);
		}
		
		return $data;
		
	}
	
	function getConsulta($result) {

		$patterns = array(	'data' => '@Cadastro\s*atualizado\s*at.{1,8}:(.*?)</td>@is');

		foreach ($patterns as $c => $p) {

			$int = preg_match($p,$result,$matches);

			if (count($matches) == 0) throw new Exception("Spider Broken at $c");

			$data[$c] = trim($matches[1]) . date(' H:i');
		}
		
		return $data;
		
	}	

}

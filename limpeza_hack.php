<?php 
  	/**
	 * Script para retirar código indesejado.
	 * @author Felipe Barth - fibbarth@gmail.com 
	 * 07/06/2013
	 */
	define('DS', DIRECTORY_SEPARATOR);
	
	// Diretório que deseja ser realizado a limpeza
	$diretorio = 'diretorio_exemplo';
	
	$log = recursivo( $diretorio );
	var_dump( '<pre>', $log );
		
	function recursivo( $caminho ){
		// Verifica se é um  diretório válido
		if( is_dir($caminho) ){
			// Faz scan de todos os arquivos e diretórios do diretório inicial.
			$arquivos = scandir($caminho);
			// Realiza um loop dentro do diretório
			foreach( $arquivos as $arquivo ){
				// exclui da verificação pasta Atual e Pai.
				if( $arquivo != '.' && $arquivo != '..' && $arquivo != 'files_infected' ){
					// Se tiver subdiretório chama a função recursiva
					if( is_dir($caminho.DS.$arquivo) ){
						recursivo( $caminho.DS.$arquivo );
					}else{
						// Se for arquivo realiza validações
						if( file_exists( $caminho.DS.$arquivo ) ){
							moveFiles( $caminho.DS.$arquivo  );
							if( verificaCodHack($caminho.DS.$arquivo ) ){
								$alterados[] = $caminho.DS.$arquivo;
							}else{
								$erros[]     = $caminho.DS.$arquivo; 
							}
						}
					}
				}
			}
		}else{
			$naoEncontrados[] = $caminho;
		}
		$log = new StdClass();
		$log->alterados  		= $alterados;
		$log->erros 			= $erros;
		$log->naoEncontrado		= $naoEncontrados;
		
		return $log;
   }
   /**
    * Função que realiza replace do código malicioso
    * @param path $arquivo
    * @return boolean
    */
   function verificaCodHack( $arquivo ){
   		$pattern = '/<\?php \$zend_framework=".*"; ?@error_reporting\(0\); \$zend_framework\("",.*\); \?>/';
   		// Verifica se possui permissão de leitura e escrita
   		if( is_writable($arquivo) && is_readable($arquivo) ){
   			$conteudo = file_get_contents($arquivo);
   			// Verifica por expressõa regular se encontra padrão no conteudo
   			if( preg_match($pattern, $conteudo) ){
   				// Realiza replace pelo padrão
   				$conteudo = preg_replace($pattern, '', $conteudo);
   				//Adiciona 
   				file_put_contents($arquivo, $conteudo, LOCK_EX);
   				return true; 
   			}
   		}
		return false;   		
   }
   
   /**
    * Função responsável por verificar nome do arquivo
    * onde caso se encaixe no padrão de arquivos suspeitos
    * irão ser deslocados para uma pasta chamada files_infected
    * para que seja verificado se podem realmente ser deletados
    */
   function moveFiles( $file ){
   		global $diretorio;
   		$infect = $diretorio.DS.'files_infected';
   		if( !is_dir($infect) ){
   			//Cria diretórios com arquivos infectados
   			mkdir($infect, 0777);
   		}
		// Verifica se o nome do arquivo casa com padrão
		if( preg_match('/^\.(%[0-9A-F]{4})*$/', basename($file)) ){
			//Movendo arquivos para pasta files_infected na raiz do diretorio atual.
  			copy($file,$infect.DS.basename($file));
  			unlink($file);
   		}
   }
?>

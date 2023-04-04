<?php

require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// // Comando para parar o Firebird no Linux
// $cmd = 'sudo systemctl stop firebird';

// // Executa o comando
// exec($cmd, $output, $return);


// // Caminho para o executável fbmgr
// $fbmgr_path = "/opt/firebird/bin/fbmgr";

// // Comando para parar o servidor Firebird
// $command = "$fbmgr_path -shutdn -force 0";

// // Executa o comando do sistema operacional para parar o servidor Firebird
// system($command);

try
{
	// ---------NOME DA EMPRESA---------
	$empresa = "COBRAS";
	$cnpj = "12.345.678/0001-00";
	$minhaempresa = "TEST"

	// Pega as datas referentes ao dia de hoje e o dia anterior
	$hoje = date("d-m-Y--H-i");
	$ontem = date('d-m-Y', strtotime('-1 day'));
	
	$nome_sistema = gethostname();
	$nome = "$minhaempresa"."_Backup_$empresa"."_$nome_sistema"."_$hoje";
	$nome_ontem = "$minhaempresa"."_Backup_$empresa"."_$nome_sistema"."_$ontem";
	$padrao_arquivo_ontem = "/$minhaempresa"."_Backup_$empresa" . "_$nome_sistema" . "_$ontem.+\.tar\.gz/";

	// Obtém a lista de arquivos no diretório
	$lista_arquivos = scandir('/diretorio/do/arquivo');
	

	// Percorre a lista de arquivos para encontrar o arquivo referente ao dia anterior
	foreach ($lista_arquivos as $nome_arquivo)
	{
		if (preg_match($padrao_arquivo_ontem, $nome_arquivo))
		{
			$caminho_arquivo_ontem = "/caminho/backup/$nome_arquivo";
			// Deleta o arquivo referente ao dia anterior
			if (unlink($caminho_arquivo_ontem)) 
			{
				exec('rm -rf ~/.local/share/Trash/*');
				echo "Arquivo deletado com sucesso.\n";
			} 
			else 
			{
				echo "Não foi possível deletar o arquivo.\n";
			}
			break;
		}
	}

	// Definir as informações do servidor FTP
	$ftp_host = "192.168.0.1";
	$ftp_user = "backup";
	$ftp_pass = "backup";
	$ftp_dir = "/diretorio/$empresa/$nome.tar.gz";

	// Definir o caminho do arquivo .fdb e o nome do arquivo
	$arquivo_fdb = '/caminho/arquivo/.fdb';

	// Definir o nome do arquivo .tar
	$arquivo_tar = "/caminho/arquivo/$nome.tar.gz";

	// Compactar o arquivo .fdb em .tar
	shell_exec("tar -czvf $arquivo_tar $arquivo_fdb");

	// Compactar o arquivo .tar em .gz
	// shell_exec("gzip $arquivo_tar");

	// Conectar ao servidor FTP
	if ($conn_id = ftp_connect($ftp_host)) 
	{
		echo "Usuário conectado com sucesso no servidor FTP. \n";
	} 
	else
	{
		email_error($cnpj, $nome, $nome_sistema);
		exit;
	};

	// Fazer login no servidor FTP
	if (ftp_login($conn_id, $ftp_user, $ftp_pass))
	{
		echo "Usuário autenticado com sucesso no servidor FTP. \n";
	} 
	else
	{
		email_error($cnpj, $nome, $nome_sistema);
		exit;
	};

	// Enviar o arquivo compactado via FTP
	ftp_put($conn_id, "$ftp_dir", $arquivo_tar, FTP_BINARY);

	// Fechar a conexão FTP
	ftp_close($conn_id);

	echo "Arquivo $nome compactado e enviado via FTP com sucesso! \n";
}
catch (Exception $e) 
{
	echo "Erro: " . $e->getMessage();
	email_error($cnpj, $nome, $nome_sistema);
}

// email_error($cnpj, $nome, $nome_sistema);
function email_error($cnpj, $nome, $nome_sistema)
{

	$mail = new PHPMailer(true);
	
	try 
	{
		// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'seuemail@gmail.com';
		$mail->Password = 'suasenha';
		$mail->Port = 587;
	
		$mail->setFrom('destinatario@gmail.com');
		$mail->addAddress('emailadicional@gmail.com');
 
		$mail->isHTML(true);
		$mail->Subject = "$cnpj - $minhaempresa BACKUP - $nome - $nome_sistema";
		$mail->Body = "ocorreu um erro na hora de executar o backup no <strong>$nome_sistema</strong>";
		// $mail->AltBody = 'email teste';

		if($mail->send()) 
		{
			echo "Email enviado com sucesso. \n";
		} 
		else 
		{
			echo "Email nao enviado. \n";
		}
	} 
	catch (Exception $e) 
	{
		echo "Erro ao enviar mensagem: {$mail->ErrorInfo} \n";
	}
}	 


// // Caminho para o executável fbmgr
// $fbmgr_path = "/opt/firebird/bin/fbmgr";

// // Comando para iniciar o servidor Firebird
// $command = "$fbmgr_path -start";

// // Executa o comando do sistema operacional para iniciar o servidor Firebird
// system($command);

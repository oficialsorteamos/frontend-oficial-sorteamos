<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\System\StateCountry;
use App\Models\System\Country;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class UtilsController extends Controller
{   
    //Função que converte o texto para o formato adequado para o Whatsapp
    public function convertTextWhatsappFormat($text)
    {
        $content = str_replace("<br>", "\n", $text);
        $content = str_replace("<strong>", "*", $content);
        $content = str_replace(" </strong>", "* ", $content);
        $content = str_replace("</strong>", "* ", $content);
        $content = str_replace("&nbsp;", " ", $content);
        $content = str_replace("&lt;", "<", $content);
        $content = str_replace("&gt;", ">", $content);

        return $content;
    }

    //Altera os parágrafos, readequando-os para ser apresentado conforme padrão do Whatsapp
    public function changeParagraphContent($text)
    {
        $newText = str_replace("<br>", "", $text);
        $newText = str_replace("<p>", "", $newText);
        $newText = str_replace("</p>", "<br>", $newText);

        return $newText;
    }

    public function changeParagraphContentTemplate($text)
    {
        $newText = str_replace("<br>", "", $text);
        $newText = str_replace("<p>", "", $newText);
        $newText = str_replace("</p>", "\n", $newText);

        return $newText;
    }

    //Faz a paginação da lista de contatos, de usuários e o que mais for necessário
    public function paginateArray($array, $perPage, $page)
    {
        $pageContacts = array_slice($array, ($page - 1) * $perPage, $page * $perPage);

        return $pageContacts;
    }

    //Traz o endereço com base no CEP
    public function getAddressApi($cep)
    {   
        try {
            //Deixa o CEP somente com números
            $cep = preg_replace('/[^0-9]/', '', $cep);
            
            $response = Http::get('https://viacep.com.br/ws/'.$cep.'/json');
            //Se não houver erro durante a requisição do endereço (Se foi digitado um CEP existente)
            if(!isset($response['erro'])) {
                $address = [];
                $address['cep'] = $response['cep']; 
                $address['logradouro'] = $response['logradouro']; 
                $address['complemento'] = $response['complemento']; 
                $address['bairro'] = $response['bairro']; 
                $address['localidade'] = $response['localidade']; 
                $address['uf'] = $response['uf']; 

                $state = null;
                $state = StateCountry::where('sta_uf', $response['uf'])
                                    ->first();
                
                $country = null;
                $country = Country::where('cou_name', 'Brasil')
                                    ->first();

                return response()->json([
                    'address' => $address,
                    'state' => $state,
                    'country' => $country
                ], 200);
            }
            else {
                return response()->json([
                    'error' => 'Não foi possível buscar o endereço. Verifique se o CEP foi digitado corretamente',
                ], 200);
            }
        } catch (e) {

        }
    }

    public function removeEmojis($string)
    {
        // Match Enclosed Alphanumeric Supplement
        $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
        $clear_string = preg_replace($regex_alphanumeric, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);
        
        // Match Supplemental Symbols and Pictographs
        $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
        $clear_string = preg_replace($regex_supplemental, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }

    //Reinicia o Websocket
    public function restartWebsocket()
    {
        $process = new Process(['supervisorctl', 'restart', 'websockets']);
        $process->setTimeout(5);
        $process->setIdleTimeout(5);
        $process->setWorkingDirectory('/etc/supervisor/conf.d/');
        
        try {
            $process->mustRun();
        
            Log::debug($process->getOutput());

            Log::debug('Rotina - Reiniciou o websocket');
        } catch (ProcessFailedException $exception) {
            Log::debug($exception->getMessage());
            Log::debug('Rotina - NÃO reiniciou o websocket');
        }
    }

    public function limitOneBreakLine($text)
    {
        $newText = str_replace(str_repeat("<p><br></p>", 10), "<p><br></p>", $text);
        $newText = str_replace(str_repeat("<p><br></p>", 9), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 8), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 7), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 6), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 5), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 4), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 3), "<p><br></p>", $newText);
        $newText = str_replace(str_repeat("<p><br></p>", 2), "<p><br></p>", $newText);

        return $newText;
    }

    //Converte um body de um request para base64
    public function convertToBase64($filePath, $fileData, $typeFile)
    {
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        // Se for um áudio
        if($typeFile == 2) {
            $dataBase64 = 'data:audio/wav/' . $type . ';base64,' . base64_encode($fileData);
        }
         //Se for uma imagem ou um sticker
        else if($typeFile == 3 || $typeFile == 7) {
            $dataBase64 = 'data:image/' . $type . ';base64,' . base64_encode($fileData);
        } // Se for um vídeo
        else if($typeFile == 4) {
            $dataBase64 = 'data:video/' . $type . ';base64,' . base64_encode($fileData);
        } //Se for um arquivo
        else if($typeFile == 5) {
            $dataBase64 = 'data:application/' . $type . ';base64,' . base64_encode($fileData);
        }

        return $dataBase64;
    }

    //Converte um body de um request para base64
    public function convertToBase64EHost($filePath, $fileData, $typeFile)
    {
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        // Se for um áudio
        if($typeFile == 2) {
            $dataBase64 = 'data:audio/ogg'.';base64,' . base64_encode($fileData);
        }
         //Se for uma imagem ou um sticker
        else if($typeFile == 3 || $typeFile == 7) {
            $dataBase64 = 'data:image/' . $type . ';base64,' . base64_encode($fileData);
        } // Se for um vídeo
        else if($typeFile == 4) {
            $dataBase64 = 'data:video/' . $type . ';base64,' . base64_encode($fileData);
        } //Se for um arquivo
        else if($typeFile == 5) {
            $dataBase64 = 'data:application/octet-stream' . $type . ';base64,' . base64_encode($fileData);
        }

        return $dataBase64;
    }
}

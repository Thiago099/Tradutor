<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tradutor</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        input[type = "submit"] {
            margin-top:20px;
        }
        .container{
            margin-top:40px;
        }
        h2{
            text-align:center;
        }
        label{
            margin-top:10px;
        }

</style>
</head>
<body>
    <?php

    if(isset($_POST['pt-br']))
    {
        $pt = json_decode($_POST['pt-br'], true);
        $en = json_decode($_POST['en-en'], true);
        $es = json_decode($_POST['es-es'], true);
    }
    
    function get_traducao($obj) {
        if(isset($_POST['pasta']) && isset($_POST['chave']) && isset($obj[$_POST['pasta']]) && isset($obj[$_POST['pasta']][$_POST['chave']]))
        {
            return $obj[$_POST['pasta']][$_POST['chave']];
        }
        return '';
    }

    function to_js($array, $tab = '	') 
    {
        $result = '';
        // uksort($array, 'strcasecmp');
        ksort($array);
        foreach($array as $key => $value)
        {
            $result .= "$tab$key: ";
            if(is_array($value))
            {
                $result .= "{\n" . to_js($value, $tab . '	') . "$tab},\n";
            }
            else
            {
                $value = str_replace('\'', '\\\'', $value);
                $result .= "'$value',\n";
            }
        }
        return $result;
    }
    
    function salvar(&$obj, $value, $path) {
        if(!isset($obj[$_POST['pasta']]))
        {
            $obj[$_POST['pasta']] = [];
        }
        $obj[$_POST['pasta']][$_POST['chave']] = $value;
        commit($path, $obj);
    }

    function excluir(&$obj, $path) {
        // if(!isset($obj[$_POST['pasta']]))
        // {
        //     $obj[$_POST['pasta']] = [];
        // }
        unset($obj[$_POST['pasta']][$_POST['chave']]);
        commit($path, $obj);
    }

    function commit($path, &$obj){
        $f = @fopen($path, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
        }
        fwrite($f,"export default {\n" . to_js($obj) ."}");
        fclose($f);
    }

    if(isset($_POST['action']))
    {
        switch ($_POST['action']) {
            case 'Salvar':
                salvar($pt, $_POST['pt'], 'pt-br.js');
                salvar($en, $_POST['en'], 'en-en.js');
                salvar($es, $_POST['es'], 'es-es.js');
                break;
            case 'Excluir':
                excluir($pt, 'pt-br.js');
                excluir($en, 'en-en.js');
                excluir($es, 'es-es.js');
                break;
        }
    }

    if(isset($_POST['pt-br']))
    {
        $pt_value = get_traducao($pt);
        $en_value = get_traducao($en);
        $es_value = get_traducao($es);
    }
    else
    {
        $pt = [];
        $en = [];
        $es = [];
        $pt_value = '';
        $en_value = '';
        $es_value = '';
    }

    
    ?>
    <div class="container">
        <h2>Tradutor</h2>
        <form action="" method="post" class="form-inline">
                <label for="pasta">Pasta</label>
                    <input type="text" id="pasta" name="pasta" class="col-sm-12" value = "<?=isset($_POST['pasta']) ? $_POST['pasta'] : 'global'?>">
                <label for="chave">Chave</label>
                    <input type="text" id="chave"  name="chave" class="col-sm-12" value = "<?=isset($_POST['chave']) ? $_POST['chave'] : ''?>">
                <?php if(isset($_POST['pt-br'])) { ?>
                <label for="pt">Portuges</label>
                <input type="text" id="pt"  name="pt" class="col-sm-12" value="<?= $pt_value ?>">
                <label for="en">Ingles</label>
                <input type="text" id="en"  name="en" class="col-sm-12" value="<?= $en_value ?>">
                <label for="es">Espanhol</label>
                <input type="text" id="es"  name="es" class="col-sm-12" value="<?= $es_value ?>">
                <?php } ?>
                <input type="submit" name="action" class="col-sm-12 btn btn-primary" value="Buscar">
                    <?php if(!isset($_POST['pt-br'])) { ?>Faça uma busca(mesmo que vazia) a primeira vez para ler os arquivos. <?php } ?>
                <input type="submit" name="action" class="col-sm-12 btn btn-primary" value="Salvar"  <?php if(!isset($_POST['pt-br'])) echo 'disabled' ?>>
                <input type="submit" name="action" class="col-sm-12 btn btn-primary" value="Excluir" <?php if(!isset($_POST['pt-br'])) echo 'disabled' ?>>
                <input id='pt-br' name='pt-br' hidden>
            <input id='en-en' name='en-en' hidden>
            <input id='es-es' name='es-es' hidden>
            <?php 
            compare($pt, $en, $es);
            compare($en, $es, $pt);
            compare($es, $pt, $en);
            function compare($a, $b, $c)
            {
                foreach($a as $key => $item) {
                    foreach($item as $key2 => $item2)
                    {
                        if(!isset($b[$key][$key2]) || !isset($c[$key][$key2]))
                        {
                            ?>
                            <input 
                                type="submit" 
                                name="action" 
                                class="col-sm-12 btn btn-danger" 
                                value="<?=$key2?>"
                                onclick = "enter('<?=$key?>', '<?=$key2?>')"
                            ?>
                            <?php
                        }
                    }
                }
            }
            ?>
        </form>
        
    </div>
    <script>
        function enter(pasta, chave)
        {
            document.getElementById('pasta').setAttribute('value',pasta);
            document.getElementById('chave').setAttribute('value',chave);
        }
    </script>
    <script type="module">

        

        import pt from './pt-br.js?v=<?=date('YmdHis', time());?>' 
        import en from './en-en.js?v=<?=date('YmdHis', time());?>'
        import es from './es-es.js?v=<?=date('YmdHis', time());?>'

        document.getElementById('pt-br').setAttribute('value', JSON.stringify(pt))
        document.getElementById('en-en').setAttribute('value', JSON.stringify(en))
        document.getElementById('es-es').setAttribute('value', JSON.stringify(es))

    </script>
</body>
</html>
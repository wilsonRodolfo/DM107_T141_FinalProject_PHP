<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    require '../vendor/autoload.php';

    $config['displayErrorDetails'] = true;
    $config['addContentLengthHeader'] = false;

    $config['db']['host'] = "localhost";
    $config['db']['user'] = "root";
    $config['db']['pass'] = "root";
    $config['db']['dbname'] = "dm107finalprojectdb";

    //$app = new \Slim\App;
    $app = new \Slim\App(["config" => $config]);
    $container = $app->getContainer();

    $container['db'] = function ($c) {
        $dbConfig = $c['config']['db'];
        $pdo = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'], $dbConfig['user'], $dbConfig['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db = new NotORM($pdo); return $db;
    };

    function listarTodosUsuarios($db){
        $usuarios = array();
        $usuarios["admin"] = "admin";
        foreach($db->clientes() as $cliente){
                $usuarios[$cliente['Nome']] = $cliente["Senha"];
        }
        return $usuarios;
    }

    $app->add(new Tuupola\Middleware\HttpBasicAuthentication([ "users" => listarTodosUsuarios($container['db']) ]));

    $app->put('/api/entrega/{id}', function (Request $request, Response $response) {
        $idEntrega = $request->getAttribute("id");
        $form = $request->getParsedBody();
        
        $entregaDB = $this->db->entregas()->where('Id',$idEntrega);
        
        if ($entregaDB->fetch()) {
            if (isset($form["NomeRecebedor"]) && isset($form["CPFRecebedor"]) && isset($form["DataHora"])) {
                $entrega = array(
                "NomeRecebedor" => $form["NomeRecebedor"],
                "CPFRecebedor" => $form["CPFRecebedor"],
                "DataHora" => $form["DataHora"]
                );
                $result = $entregaDB->update($entrega);
                return $response->withStatus(204);
            } else {
                if(!isset($form["NomeRecebedor"])){
				    echo ("Campo obrigatório: NomeRecebedor. Digite esse campo novamente.\n\n");
                }
                if(!isset($form["CPFRecebedor"])){
                    echo ("Campo obrigatório: CPFRecebedor. Digite esse campo novamente.\n\n");
                }
                if(!isset($form["DataHora"])){
                    echo ("Campo obrigatório: DataHora da entrega. Digite esse campo novamente.\n\n");
                }
                return $response->withStatus(400);
            }  
        } else {
            return $response->withStatus(404);
        }
    });

    $app->delete('/api/entrega/{id}', function (Request $request, Response $response) {
        $idEntrega = $request->getAttribute("id");

        $entregaDB = $this->db->entregas()->where('Id',$idEntrega);
        if ($entregaDB->fetch()) {
            $deleted = $entregaDB->delete();
            return $response->withStatus(204);
        } else {
            return $response->withStatus(404);
        }
    });
    
    $app->run();
?>
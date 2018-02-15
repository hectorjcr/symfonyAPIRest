<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
                    'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request) {
        $helpers = $this->get(Helpers::class);

        //Recibimos json poer POST (credenciales)
        $json = $request->get('json', null);

        //Array a devolver por defecto
        $data = ['status' => 'Error', 'data' => 'enviar json via POST!!'];

        if ($json != null) {
            //me haces login
            //convertimos el json a objeto php
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Este email no es valido!!";
            $validateEmail = $this->get('validator')->validate($email, $emailConstraint);
            
            //Cifrar la contraseña
            $pwd = hash('sha256',$password);

            if ($email != null && count($validateEmail) == 0 && $password != null) {
                $jwt_auth = $this->get(JwtAuth::class);
                if ($getHash == null || $getHash == false) {
                    $signup = $jwt_auth->signup($email, $pwd);
                } else {
                    $signup = $jwt_auth->signup($email, $pwd, $getHash);
                }
                return $this->json($signup);
            } else {
                $data = ['status' => 'Error', 'data' => 'Email or password incorrect'];
            }
            //$data = ['status'=>'Success', 'data'=>'OK!!'];
        }
        return $helpers->json($data);
    }

    public function pruebasAction(Request $request) {
        
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);        
        if ($token && $jwt_auth->checkToken($token)) {
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll();

            $helpers = $this->get(Helpers::class);
            return $helpers->json(array(
                'status'=>'success',
                'users' => $users
            ));
        } else {
            return $helpers->json(array(
                'status'=>'success',
                'code' => 400,
                'data' => 'Authorization no Valid!'
            ));
        }
    }

}

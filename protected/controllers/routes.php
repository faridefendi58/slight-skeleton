<?php
// Modules Routes
foreach(glob($settings['settings']['basePath'] . '/modules/*/controllers/routes.php') as $mod_routes) {
    require_once $mod_routes;
}

// Extensions routes
foreach(glob($settings['settings']['basePath'] . '/extensions/*/controllers/routes.php') as $ext_routes) {
    require_once $ext_routes;
}

$app->get('/[{name}]', function ($request, $response, $args) {
    
	if (empty($args['name']))
		$args['name'] = 'index';

    $settings = $this->get('settings');

    if (!is_array($settings['db'])) {
        return $this->response->withRedirect( 'install.php' );
    }

    if (!file_exists($settings['theme']['path'].'/'.$settings['theme']['name'].'/views/'.$args['name'].'.phtml')) {
        return $this->response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found!');
    }

    if (isset($_GET['e']) && $_GET['e'] > 0) { // editing procedure
        $view_path = $settings['theme']['path'] . '/' . $settings['theme']['name'] . '/views';
        if (file_exists($view_path.'/'.$args['name'] . '.phtml')) {
            if (file_exists($view_path.'/staging/'.$args['name'] . '.ehtml')) {
                unlink($view_path.'/staging/'.$args['name'] . '.ehtml');
            }
            $cp = copy($view_path.'/'.$args['name'] . '.phtml', $view_path.'/staging/'.$args['name'] . '.ehtml');
            if ($cp) {
                $content = file_get_contents($view_path.'/staging/'.$args['name'] . '.ehtml');
                $parsed_content = str_replace(array("{{", "}}"), array("[[", "]]"), $content);

                $update = file_put_contents($view_path.'/staging/'.$args['name'] . '.ehtml', $parsed_content);
            }

            return $this->view->render($response, 'staging/' . $args['name'] . '.ehtml', [
                'name' => $args['name'],
                'mpost' => $model,
                'request' => $_GET
            ]);
        }
    }

    return $this->view->render($response, $args['name'] . '.phtml', [
        'name' => $args['name'],
        'request' => $_GET
    ]);
});

$app->post('/kontak-kami', function ($request, $response, $args) {
    $message = 'Pesan Anda gagal dikirimkan.';
    if (isset($_POST['Contact'])){
        //send mail to admin
        $message = 'Pesan Anda berhasil dikirim. Kami akan segera merespon pesan Anda.';
    }

    echo $message; exit;
});

$app->post('/tracking', function ($request, $response, $args) {
    if (isset($_POST['s'])){
        $model = new \Model\VisitorModel('create');
        $model->client_id = 0;
        if(!empty($_POST['s'])){
            $model->session_id = $model->getCookie('_ma',false);
            if (!empty($model->cookie)){
                $model->date_expired = $model->cookie;
            } else {
                //Yii::app()->request->cookies->remove('_ma');
                $model->date_expired = date("Y-m-d H:i:s",time()+1800);
            }
        }
        $model->ip_address = $_SERVER['REMOTE_ADDR'];
        $model->page_title = $_POST['t'];
        $model->url = $_POST['u'];
        $model->url_referrer = $_POST['r'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->platform = $_POST['p'];
        $model->user_agent = $_POST['b'];

        require_once $this->settings['basePath'] . '/components/mobile_detect.php';
        $mobile_detect = new \Components\MobileDetect();
        $model->mobile = ($mobile_detect->isMobile())? 1 : 0;

        $create = \Model\VisitorModel::model()->save(@$model);

        if ($create > 0) {
            if ($model->session_id == 'false' || empty($model->session_id)) {
                $model2 = \Model\VisitorModel::model()->findByPk($model->id);
                $model2->session_id = md5($create);
                $update = \Model\VisitorModel::model()->update(@$model2);
                //$cookie_time = (3600 * 0.5); // 30 minute
                //setcookie("ma_session", $model->session_id, time() + $cookie_time, '/');
            }
            //set notaktif
            $model->deactivate($model->session_id);
            // update the current record
            if (!is_object($model2))
                $model2 = \Model\VisitorModel::model()->findByPk($model->id);
            $model2->active = 1;
            $update2 = \Model\VisitorModel::model()->update($model2);

            echo $model2->session_id;
        }else{
            echo 'failed';
        }

        exit;
    }
});

$app->post('/install', function ($request, $response, $args) {
    $settings = $this->get('settings');

    if (is_array($settings['db'])) {
        return $this->response->withRedirect( 'index' );
    }
    
    if (isset($_POST['host']) && isset($_POST['dbname']) && isset($_POST['username'])) {
        $application = new \Components\Application();
        $setDb = $application->setDb( $_POST );

        if ($setDb) {
            $uri = $request->getUri();
            $_POST['base_url'] = $uri->getScheme().'://'.$uri->getHost().$uri->getBasePath();
            if (!empty($uri->getPort()))
                $_POST['base_url'] .= ':'.$uri->getPort();

            $createDb = $application->createDb( $_POST );

            return $this->response->withRedirect( 'index' );
        }
    }

    return $this->response
        ->withStatus(500)
        ->withHeader('Content-Type', 'text/html')
        ->write('Page not found!');
});

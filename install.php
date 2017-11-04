<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SlightSite - Install</title>

    <link href="/protected/modules/panel-admin/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/protected/modules/panel-admin/assets/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
include_once __DIR__ .'/protected/components/application.php';
$app = new \Components\Application();
?>
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <div class="mt20"></div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>Instalasi</h2>
                </div>
                <div class="panel-body">
                    <?php if (is_array($app->getDbs())): ?>
                        <div class="alert alert-warning">
                            <p>SlightSite sudah sukses terinstall pada proses instalasi sebelumnya.<br/>Mohon hapus file <i>install.php</i> pada direktori file website Anda.</p>
                        </div>
                    <?php else: ?>
                    <p>Silakan masukkan beberapa informasi detail koneksi database :</p>
                    <form action="/install" method="post">
                        <div class="form-group">
                            <label for="database_name">Nama Database:</label>
                            <input type="text" name="dbname" class="form-control" id="database_name" value="slightsite">
                            <span class="help-block">Nama database yang Anda inginkan untuk SlightSite.</span>
                        </div>
                        <div class="form-group">
                            <label for="database_username">Username Database:</label>
                            <input type="text" name="username" class="form-control" id="database_username" value="root">
                            <span class="help-block">Username database Anda.</span>
                        </div>
                        <div class="form-group">
                            <label for="database_password">Password Database:</label>
                            <input type="password" name="password" class="form-control" id="database_password">
                            <span class="help-block">Password database Anda.</span>
                        </div>
                        <div class="form-group">
                            <label for="database_host">Host Database:</label>
                            <input type="text" name="host" class="form-control" id="database_host" value="localhost">
                            <span class="help-block">Host database Anda.</span>
                        </div>
                        <div class="form-group">
                            <label for="database_prefiks">Prefiks:</label>
                            <input type="text" name="tablePrefix" class="form-control" id="database_prefiks" value="tbl_">
                            <span class="help-block">Prefik atau awalan nama tabel untuk database Anda.</span>
                        </div>
                        <button type="submit" class="btn btn-info">Install Sekarang</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt20"></div>
        </div>
    </div>
</div>
</body>
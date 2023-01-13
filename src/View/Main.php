<?php

namespace App\View;

class Main extends Base
{
    public function container($data = [])
    {
        ?>
            <div id="page-container" class="sidebar-l sidebar-o side-scroll header-navbar-fixed">
              

                <!-- Main Container -->
                <main id="main-container">
                    <!-- Page Header -->
                    <div class="content bg-gray-lighter">
                        <div class="row items-push">
                            <div class="col-sm-7">
                                <h1 class="page-heading">
                                    Страница загрузки прайс листа
                                </h1>
                            </div>
                        </div>
                    </div>
                    <!-- END Page Header -->

                    <!-- Page Content -->
                    <div class="content">
                    <form class="form-horizontal push-10-t" enctype="multipart/form-data" action="/upload" method="post">
                        <div class="col-sm-9">
                            <div class="form-material">
                                <select class="form-control" id="material-select" name="provider" size="1">
                                    <option>...</option>                                    
                                    <option value="0">Seakoo</option>
                                    <option value="1">Temptools</option>
                                    <option value="2">Magkaznu</option>
                             
                                </select>
                                <label for="material-select">Выберите поставщика</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12" for="example-file-input">Выберите файл</label>
                            <div class="col-xs-12">
                                <input type="file" id="example-file-input" name="pricelist">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-9">
                                <button class="btn btn-sm btn-primary" type="submit" name="SubmitButton">Загрузить</button>
                            </div>
                        </div>
                    </form>
                    <!-- END Page Content -->
                </main>
                <!-- END Main Container -->

                <!-- Footer -->
                <footer id="page-footer" class="content-mini content-mini-full font-s12 bg-gray-lighter clearfix">
                    <div class="pull-right">
                        Created <i class="fa fa-heart text-city"></i> by <a class="font-w600" href="" target="_blank">Dinara</a>
                    </div>
                    <div class="pull-left">
                        <a class="font-w600" href="" target="_blank">DT</a> &copy; <span class="js-year-copy"></span>
                    </div>
                </footer>
                <!-- END Footer -->
            </div>
        <?php
    }


}
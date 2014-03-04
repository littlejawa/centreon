<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
namespace Controllers;

/**
 * Tools controller
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Controllers
 */
class ToolsController extends \Centreon\Core\Controller
{
    /**
     * Action for compile LESS
     *
     * @method GET
     * @route @\.css$
     */
    public function lessAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $route = $router->request()->pathname();
        $response = $router->response();
        /* Get path to  */
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $route = str_replace($baseUrl, '/', $route);
        $route = str_replace('css', 'less', $route);
        /* Remove min */
        $route = str_replace('.min.', '.', $route);
        $centreonPath = realpath(__DIR__ . '/../../www/');
        if (false === file_exists($centreonPath . $route)) {
            $this->notFoundAction();
            return;
        }
        
        /* Response compiled CSS */
        $response->header('Content-Type', 'text/css');
        $less = new \Less_Parser();
        $less->parseFile($centreonPath . $route);
        $response->body($less->getCss());
    }

    /**
     * Action for display image from database
     *
     * @method GET
     * @route /uploads/[*:image][png|jpg|gif|jpeg:format]
     */
    public function imageAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        $params = $router->request()->paramsNamed();
        $filename = $params['image'] . $params['format'];
        $query = 'SELECT b.binary, b.mimetype
            FROM binaries b, view_img i
            WHERE i.img_name = :filename
                AND i.binary_id = b.binary_id';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':filename', $filename, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        if (false === $row) {
            $this->notFoundAction();
            return;
        }

        /* Write file in filesystem for serve file by http server */
        $centreonPath = realpath(__DIR__ . '/../../www/');
        $filefs = $centreonPath . '/uploads/' . $filename;
        if (false === file_exists($filename)) {
            file_put_contents($filefs, $row['binary']);
        }
        $router->response()->header('Content-Type', $row['mimetype']);
        $router->response()->body($row['binary']);
        $router->response()->send();
    }

    /**
     * Page 404
     *
     * @method GET
     * @route 404
     */
    public function notFoundAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $response = $di->get('router')->response();
        $response->code(404);
        $tpl = $di->get('template');
        $tpl->display('404.tpl');
    }
}

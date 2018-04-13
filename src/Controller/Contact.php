<?php

namespace CascadiaPHP\Site\Controller;

use League\Plates\Template\Template;
use Psr\Http\Message\ResponseInterface;
use Spatie\SchemaOrg\Schema;

class Contact extends Controller
{

    protected $cssPath = '/css/pages/contact.css';

    public function view(): ResponseInterface
    {
        $this->seo()
            ->setTitle('Contact the team behind Cascadia PHP 2018')
            ->setDescription('Cascadia PHP is run by a non-profit created by the PHPDX Portland PHP usergroup');

        return $this->render('/pages/contact');
    }
}

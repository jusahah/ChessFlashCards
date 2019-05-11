<?php

namespace App\Http\Controllers\Fractal;

use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;

class FractalController extends Controller
{
    protected $fractal;
    protected $extraFields;

    public function __construct() {
        $baseUrl = url('api/v1');

        $this->fractal = new Manager();
        $this->fractal->setSerializer(new DataArraySerializer($baseUrl));

        if (isset($_GET['include'])) {
            $this->fractal->parseIncludes($_GET['include']);
        }
    }
}
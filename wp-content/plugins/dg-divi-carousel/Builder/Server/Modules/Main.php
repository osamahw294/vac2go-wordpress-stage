<?php

namespace DICA\Server\Modules;


use DICA\Server\Modules\DiviCarousel\DiviCarousel;
use DICA\Server\Modules\DiviCarouselItem\DiviCarouselItem;

class Main
{
    public function __construct()
    {
        add_action('divi_module_library_modules_dependency_tree', [$this, 'register_to_deps']);
    }

    public function register_to_deps($dependency_tree)
    {
        $dependency_tree->add_dependency(new DiviCarouselItem());
        $dependency_tree->add_dependency(new DiviCarousel());
    }
}

new Main();

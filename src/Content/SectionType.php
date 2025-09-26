<?php
declare(strict_types=1);


namespace App\Content;


enum SectionType: string
{
    case header = 'header';
    case hero = 'hero';
    case services = 'services';
    case process = 'process';
    case cta = 'cta';
    case pricing = 'pricing';
    case stats = 'stats';
    case testimonials = 'testimonials';
    case contact = 'contact';
    case logo_cloud = 'logo_cloud';
    case projects = 'projects';
    case footer = 'footer';
}

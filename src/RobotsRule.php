<?php

namespace ThreeOhEight\Seo;

enum RobotsRule: string
{
    case All = 'all';
    case Index = 'index';
    case NoIndex = 'noindex';
    case Follow = 'follow';
    case NoFollow = 'nofollow';
    case None = 'none';
    case NoArchive = 'noarchive';
    case NoSnippet = 'nosnippet';
    case NoImageIndex = 'noimageindex';
}

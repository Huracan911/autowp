<?php

namespace Application;

use Zend\Http\PhpEnvironment\Request;

class Language
{
    /**
     * @var string
     */
    private $defaultLanguage = 'en';

    /**
     * @var string
     */
    private $language;

    public function __construct($request, array $hosts)
    {
        $this->language = $this->defaultLanguage;

        $map = [];
        foreach ($hosts as $language => $host) {
            $map[$host['hostname']] = $language;
            foreach ($host['aliases'] as $alias) {
                $map[$alias] = $language;
            }
        }

        if ($request instanceof Request) {
            /* @phan-suppress-next-line PhanUndeclaredMethod */
            $hostname = $request->getServer('HTTP_HOST');
            if (isset($map[$hostname])) {
                $this->language = $map[$hostname];
            }
        }
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
}

<?php

namespace SymfonyRollbarBundle\Twig;

use Rollbar\RollbarJsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

class RollbarExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * RollbarExtension constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        try {
            $config = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

            if (!empty($config['rollbar_js']['enabled'])) {
                $this->config = $config;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        if (empty($this->config)) {
            return [];
        }

        return [
            new \Twig_SimpleFunction('rollbarJs', [$this, 'rollbarJs'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return string
     */
    public function rollbarJs()
    {
        $helper = new RollbarJsHelper($this->config['rollbar_js']);

        $script = "<script>{{config}};\n{{rollbar-snippet}}</script>";
        $script = strtr($script, [
            '{{config}}'          => $helper->configJsTag(),
            '{{rollbar-snippet}}' => $helper->jsSnippet(),
        ]);

        return $script;
    }
}

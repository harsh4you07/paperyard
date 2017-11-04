<?php

namespace Paperyard\Controllers\Misc;

use Paperyard\Controllers\BasicController;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Index
 * @package Paperyard\Controllers\Misc
 */
class Index extends BasicController
{
    /**
     * Index constructor.
     * @param Twig $view
     * @param LoggerInterface $logger
     * @param Messages $flash
     */
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('bootstrap-notify.min');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->view->render($response, 'index.twig', $this->render());
        return $response;
    }

    /**
     * render
     * @return array data to render the view
     */
    public function render()
    {
        return [
            'plugins' => parent::getPlugins(),
            'languageFlag' => parent::getLanguageFlag(),
            'scannedToday' => $this->documentsScanned(),
        ];
    }

    /**
     * documentsScanned
     *
     * Counts the total scanned documents.
     *
     * @return int total scanned documents
     */
    private function documentsScanned()
    {
        return \Paperyard\Models\Log\File::distinct()->get(['fileContent'])->count();
    }
}
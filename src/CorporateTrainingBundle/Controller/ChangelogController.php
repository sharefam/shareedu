<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\ChangelogController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class ChangelogController extends BaseController
{
    public function listAction(Request $request)
    {
        $rootDir = $this->getParameter('kernel.root_dir');
        $changelogUrl = $rootDir.'/../src/CorporateTrainingBundle/CHANGELOG';
        $changelogFile = fopen("{$changelogUrl}", 'r');

        $changelogRows = array();
        while (!feof($changelogFile)) {
            $changelogRows[] = fgets($changelogFile);
        }

        fclose($changelogFile);

        return $this->render('change-log/list.html.twig', array(
            'logs' => $changelogRows,
        ));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2018
 * Time: 1:43 PM
 */

namespace AppBundle\Controller\Formula;


use AppBundle\AppException\AppException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FormulaController extends Controller
{

    /**
     * @Route("/misc/formual/lga", name="lga_formula")
     */
    public function lgaFormulaAction()
    {
        $lgaFormulas = array();
        try {
            $lgaFormulas = $this->get('app.formula_generator')->generateLGAFormula();
        } catch (AppException $e) {
            $this->get('logger')->alert($e->getMessage());
        }
        return $this->render('formula/lga_formula.html.twig'
            , [
                'lgaFormulas' => $lgaFormulas
            ]);
    }

}
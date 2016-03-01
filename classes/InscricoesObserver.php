<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 30/10/15
 * Time: 10:44
 */
use TiagoGouvea\PHPObserver\Observer;
use TiagoGouvea\PHPObserver\Subject;

class InscricaoObserver implements Observer
{
    /**
     * This is the only method to implement as an observer.
     * It is called by the Subject (usually by SplSubject::notify() )
     * @param Subject $subject
     * @param $action
     */
    public function update($subject, $action)
    {
        // Saber aqui quem deverá ser avisado
        // Agile, RD, etc.....
        // Talvez eu apenas notifique cada um, aqui podia ter um array também de caras? configuração/classe?

        if (TGO_EVENTO_RDSTATION===true){
            // Registrar no RD que algo aconteceu
            lib\RdStation::update($subject,$action);
        }

//        if (TGO_EVENTO_AGILECRM===true){
            // Registrar no Agile que algo aconteceu
//            lib\AgileCrm::update($subject,$action);
//        }
    }
}
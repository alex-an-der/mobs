<?php 
namespace ypum;


class mailmanager{

    function __construct($dateiname, $titel, $conf=null){
        require_once(__DIR__."/../inc_main.php");
        include_once(__DIR__.'/../../components/navbar_config.php');

        $libPath = $conf->getYpumRoot()."/yback/lib";

        $vorschau = '';
        $chckd='';

        $bHtmlmail = false;

        $alertcolor= "";
        $alerttext = "";

        $absendeadresse = "";
        $absendename = "";
        $betreff = "";
        $empfaengertest = "";

        if(isset($_POST['MACH_SAVE']) || isset($_POST['MACH_TESTMAIL'])){// Hole POST

            $mailvorlage = array();
            $mailvorlage['mailtext'] = $_POST['mailtext'];
            $vorschau = $_POST['mailtext'];

            if(isset($_POST['txformat'])){
                $bHtmlmail = true;
                $mailvorlage['txformat'] = 'html';
                
            }else{
                $bHtmlmail = false;
                $mailvorlage['txformat'] = 'text';

            }

            $absendeadresse = $_POST['absendeadresse'];;
            $absendename = $_POST['absendename'];
            $betreff = $_POST['betreff'];
            $empfaengertest = $_POST['mailan'];

            $mailvorlage['absendeadresse'] = $absendeadresse;
            $mailvorlage['absendename'] = $absendename;
            $mailvorlage['betreff'] = $betreff;
            $mailvorlage['empfaengertest'] = $empfaengertest;
            
            $conf->save($dateiname,$mailvorlage);
        }

        $dummylink="";

        @$mailvorlage = $conf->load($dateiname);

        if(!empty($mailvorlage)){
            $vorschau = $mailvorlage['mailtext'];

            $absendeadresse = $mailvorlage['absendeadresse'];
            $absendename = $mailvorlage['absendename'];
            $betreff = $mailvorlage['betreff'];
            $empfaengertest = $mailvorlage['empfaengertest'];
        
            $dummylink = $conf->getYpumRoot()."/dummypath/dummyfile.php?ID=42&token=".md5(random_bytes(39));

            if(strcmp($mailvorlage['txformat'],'html')==0){
                $bHtmlmail = true;
            }else{
                $bHtmlmail = false;
            }
        }
     

        if(isset($_POST['MACH_TESTMAIL'])){
 
            $empfaenger = $empfaengertest;
            $betreff = str_replace('##LINK##',$dummylink,$betreff);
            $mailtext = str_replace('##LINK##',$dummylink,$_POST['mailtext']);
            $absender = $absendename."<".$absendeadresse.">";

            $headers   = array();
            $headers[] = "MIME-Version: 1.0";
            if($bHtmlmail){
                $headers[] = "Content-type: text/html; charset=utf-8";
            }else{
                $headers[] = "Content-type: text/plain; charset=utf-8";
            }
            $headers[] = "From: {$absender}";

            $mailres = mail($empfaenger, $betreff, $mailtext,implode("\r\n",$headers));

            if(!$mailres)
            {

                $alertcolor= 'danger';
                $alerttext = 'Fehler beim Mailversandt!';
            }
            else
            {
                $alertcolor= 'success';
                $alerttext = 'Die Mail wurde erfolgreich versandt.';
            }
        }

        $sourcecode = htmlentities($vorschau);
        if($bHtmlmail){
            $chckd = 'checked';
        }else{
            $chckd = '';
            // HTML-Zeichen nicht interpretieren:
            $vorschau = htmlentities($vorschau);
            // Zeilenwechsel als HTML ausgeben
            $vorschau = nl2br ($vorschau);
        }
        $vorschau = str_replace('##LINK##',$dummylink,$vorschau);

        require(__DIR__."/../code/mail_view.php");
        $conf->getFooter();
        echo ("</body></html>");
    } // END construct
} // END class



?> 

  

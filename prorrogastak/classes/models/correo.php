<?php
namespace tool_prorrogastak\models;

// require(dirname(dirname(__FILE__)).'/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 *
 */
class correo
{

  public function __construct()
  {
    // code...
  }

  public function correo_envio(){
    //Query para el curso y fechas 
    $query = "Select c.id, c.shortname, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fechainicio,
    DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fechaporroga,
    DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechafin,
    DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha_prorroga,
    DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fechainicioe
    from (select @s:=0) as s, mdl_course c
    INNER JOIN mdl_customfield_data  cd ON cd.instanceid = c.id
    where c.visible = 1 and cd.fieldid = 44 and c.id = 241"; 

      global $DB;
      $result = $DB->get_records_sql($query);

      //Url temporal 
      $url = 'https://calidad.laucmi.telefonicaed.pe/course/view.php?id=';

      foreach ($result as $it) {
        $urltemp = $url.$it->id;
        $id = $it->id;
        $fechaFin = $it->fechafin; 
        $fechapro = $it->fechaporroga;
        $fechaInicio = $it->fechainicio;
        $fechaold = $it->fecha_prorroga;
        $fechainicio = $it->fechainicioe; 

        //Query para saber quienes son los stakeholders 
        $query2 = "SELECT  @s:=@s + 1 id_auto, concat(u.firstname,' ', u.lastname) as nombre, u.email, c.shortname, c.fullname,
                  asg.roleid, asg.userid, r.shortname as stakholder FROM
                  (select @s:=0) as s,
                  mdl_user u
                  INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
                  INNER JOIN mdl_context as con on asg.contextid = con.id
                  INNER JOIN mdl_course c on con.instanceid = c.id
                  INNER JOIN mdl_role r on asg.roleid = r.id
                  where c.shortname = '$it->shortname' and r.shortname = 'stakeholder'";
        $result2 = $DB->get_records_sql($query2);

        echo '<pre>';
          print_r($result2);
        echo '</pre>';

          foreach ($result2 as $it2) {
            $nombre = $it2->nombre;
            $body = $urltemp;
            $subject = $it2->fullname;

            //emailuser configuración 
            $emailuser->email = $it2->email;
            $emailuser->id = -99;
            $emailuser->maildisplay = true;
            $emailuser->mailformat = 1;

            date_default_timezone_set("America/Guatemala");
            $fechaAct = date("d/m/Y H:i"); //w para los dias de la semana
            $fechaViernes = date("w H:i");

            //Imagen 
            $String ="<img src='http://54.161.158.96/local/img/banner.jpg'";  

             //Texto para el recordatorio
             $string1 = ""; 
             $string1 .= $String."\n";
             $string1 .= "<br>"; 
             $string1 .= "<br>"; 
             $string1 .= "<div style='color: orange; font-size: 18px; font-family: Century Gothic;'> $nombre </div>";
             $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Te informamos que la fecha de finalización del curso <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $subject, </span> se ha extendido hasta <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechaFin. </span> </div>";
             $string1 .= "<br>"; 
             $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Cualquier duda o comentario puedes escribirnos a cmi-laucmi@somoscmi.com \n </div>";
             $string1 .= "<br>"; 
             $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Atentamente, \n </div>";
             $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> laUcmi \n </div>";

            //Comparaciones de fechas para el envio del correo electronico
            if($fechaAct == $fechainicio){
              if($fechaold != '31/12/1969 17:00')
              {
                  $email = email_to_user($emailuser,' laUcmi ','Extension de fecha de '.$subject, $string1);
                  echo "Correo enviado";
              }
            }else
            {
              echo "Correo no enviado";
            }
          }
        }
      }
    }
?>

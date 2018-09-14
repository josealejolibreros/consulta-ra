
<?php 

	
function get_student_information($cod_est){

   $curl = curl_init();

   curl_setopt($curl, CURLOPT_URL, "https://swebse32.univalle.edu.co/sra/paquetes/herramientas/wincombo.php?opcion=estudianteConsulta&patron=$cod_est&variableCalculada=0");

   curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	  'Cookie: PHPSESSID=7aedce9584730851734627ae734f6aec'
   ));

   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

   $resp = curl_exec($curl);

   curl_close($curl);
   
   return $resp;
   
}

function read_student_info_csv(){

	$student_info = array();
	$row = 1;
	if (($handle = fopen("students.csv", "r")) !== FALSE) {
  		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    		$row++;
        	array_push($student_info, $data[2].'-'.$data[1].'-'.$data[0]);
  		}
  		fclose($handle);
	}

	
	return $student_info;
}

function search_student_info($info){

	$info_student_arr = explode("-", $info);
	$st_apellidos_sp = $info_student_arr[0];
	$st_apellidos = str_replace(" ","%20",$st_apellidos_sp);
	$st_nombres_sp = $info_student_arr[1];
	$st_nombres = str_replace(" ","%20",$st_nombres_sp);
	$st_documento = $info_student_arr[2];
	
	$param = $st_apellidos.'-'.$st_nombres;
	echo $param;
	$html = get_student_information($param);
	
	echo $html;

	$dom = new domDocument; 
   
	$dom->loadHTML($html); 
   
   	$dom->preserveWhiteSpace = false; 
   
   	$tables = $dom->getElementsByTagName('table'); 
   
   	$rows = $tables->item(0)->getElementsByTagName('tr'); 

	   $j = 9;
	   
	   for($i = 2; $i < count($rows); $i++){
			$cols = $rows[2]->getElementsByTagName('td'); 
			
			$cod_per = (string)$cols->item($j)->nodeValue;
			$cod_estu = (string)$cols->item($j+1)->nodeValue;
			
			$documento = explode(" ", (string)$cols->item($j+2)->nodeValue);
			$tipo_doc = $documento[0];
			$numero_doc = $documento[1];
			$nombre = str_replace(" ","+",(string)$cols->item($j+3)->nodeValue);
			
			$apellido = str_replace(" ","+",(string)$cols->item($j+4)->nodeValue);
			$apellido = (string)$cols->item($j+4)->nodeValue;
		
		
			echo 'Codigo persona '.$cod_per.'<br />';	
			echo 'Codigo estudiante'.$cod_estu.'<br />';
			echo 'Tipo documento '.$tipo_doc.'<br />';
			echo 'Numero documento '.$numero_doc.'<br />';
			echo 'Nombres '.$nombre.'<br />';
			echo 'Apellidos '.$apellido.'<br />';
			
		
			$sede_plan_modo = (string)$cols->item($j+5)->nodeValue;
			$tipo_sede = explode("-", $sede_plan_modo);
		
			$plan = $tipo_sede[0];
			$sede = $tipo_sede[1];
			$mod = $tipo_sede[2];
		
			echo 'Plan '.$plan.'<br />';
			echo 'Sede '.$sede.'<br />';
			echo 'Modalidad '.$mod.'<br />';
			echo '<hr />'; 

			$ocurrencia_igual_nombre_diferente_documento="NO";

			$nombre_wsp=str_replace("%20"," ",$st_apellidos);
			$apellido_wsp=str_replace("%20"," ",$st_nombres);

			$nombre_wsp_reg=str_replace("+"," ",nombre);
			

			if($nombre_wsp === $nombre_wsp_reg && $apellido_wsp === $apellido && $numero_doc !== $st_documento){
				$ocurrencia_igual_nombre_diferente_documento="SI";
			}

			if($numero_doc === $st_documento){
				$info_student_active = [];
				echo "check id";



				$info_student = search_student_info_each_program(
					$cod_per,$cod_estu,$tipo_doc,$numero_doc,$nombre,$apellido,$plan,$sede,$mod,$ocurrencia_igual_nombre_diferente_documento);
				



					echo 'Info student: '.$info_student;

					$info_student_sp = explode(",", $info_student);

					$info_student_active = $info_student;
				if($info_student_sp[8]=="ACTIVO"){
					echo "ENTRA Y VERIFICA ACTIVO";
					
					return $info_student_active;
				}

				
				

			} 

			$j = $j + 7;
	   }
	   return $info_student_active;
   	




    
}


function search_student_info_each_program($cod_per,$cod_estu,$tipo_doc,$numero_doc,$nombre,$apellido,$plan,$sede,$mod,$ocurrencia_igual_nombre_diferente_documento){
	$chp2 = curl_init();

    curl_setopt($chp2, CURLOPT_URL, "https://swebse32.univalle.edu.co/sra//paquetes/academica/index.php");

    curl_setopt($chp2, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($chp2, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($chp2, CURLOPT_HTTPHEADER, array(
	  'Cookie: PHPSESSID=7aedce9584730851734627ae734f6aec',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0',
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'DNT: 1'
   ));

   	curl_setopt($chp2, CURLOPT_POST, 1);

   	$fields = "deu_est_per_codigo=$cod_per&codigo_estudiante=$cod_estu&wincombomep_codigo_estudiante=$nombre++$apellido->+$plan-$sede-$mod&modulo=Academica&accion=Consultar+Estudiante";
   	
   	curl_setopt($chp2, CURLOPT_POSTFIELDS, $fields);

	   
   	$respo2 = curl_exec($chp2);

   	curl_close($chp2);

   	echo $respo2;
	
	$dom2 = new domDocument; 

	$dom2->loadHTML($respo2); 
	
    $dom2->preserveWhiteSpace = false; 
   
    $tables = $dom2->getElementsByTagName('table'); 
   
    $rows = $tables->item(0)->getElementsByTagName('tr'); 
   
    $cols = $dom2->getElementsByTagName('td'); 
    $estado = 'INACTIVO';
    echo 'El estado es '.$estado.'<br />';
  
      
	for ($i = 0; $i <= 100; $i++) {
	    $periodo_academico = (string)$cols->item($i)->nodeValue;
	    #echo $periodo_academico.'<br />';
	    if(substr_count($periodo_academico,"AGOSTO/2018 - DICIEMBRE/2018") == 1){
	    	//echo 'I '.$i.' '.$periodo_academico;
			$estado = 'ACTIVO';
			
	    }
		
	}

	echo '<br>EL ESTADO ES '.$estado.'<br />';
	$nombre_sp = str_replace("+"," ",$nombre);
	$apellido_sp = str_replace("+"," ",$apellido);

	$information_student = "$cod_estu,$tipo_doc,$numero_doc,$nombre_sp,$apellido_sp,$plan,$sede,$mod,$estado,$ocurrencia_igual_nombre_diferente_documento";


	

	return $information_student;
}

function write_student_information_to_csv($student_info_arr){

	$file = fopen("estudiantes.csv","w");

	foreach ($student_info_arr as $line)
  	{
	  	fputcsv($file,explode(',',$line));
  	}

	fclose($file);
		
	echo '<br>Fin '.$estado;
}

	$student_names_arr = read_student_info_csv();

	
	$list_student = array();
	foreach($student_names_arr as $info_student)
	{
		$info_student_active = search_student_info($info_student);
		array_push($list_student, $info_student_active);
	}

	write_student_information_to_csv($list_student);


	/*
	$list_student = array();

	foreach ($student_codes_arr as $student_cod)
	{
		$info_student = search_student_info($student_cod);
		array_push($list_student, $info_student);

	}	

	write_student_information_to_csv($list_student);
	*/

?> 

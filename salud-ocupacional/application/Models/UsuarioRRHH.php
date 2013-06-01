<?php
class Models_UsuarioRRHH extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "nompue"){
                $stmt = $db->prepare("SELECT DNI, Ap_Paterno, Ap_Materno, Nombres
                        FROM UVW_DATLAB WHERE 
                        Id_cia = '".$datos["p_idCompania"]."' AND 
                        Id_Loc = '".$datos["p_idLocalidad"]."' AND
                        DNI = '".$datos["p_dni"]."'");
            }elseif(isset($datos["p_dni"])){
                /* PARA CRONOGRAMA INDIVIDUAL*/
                $stmt = $db->prepare("SELECT 
                            convert(varchar(25), Fecha_Ingreso, 21) AS fechaingreso, 
                            convert(varchar(25), Fe_Ini_Vac, 21) AS Fe_Ini_Vacs, 
                            convert(varchar(25), Fe_Fin_Vac, 21) AS Fe_Fin_Vacs, 
                            * 
                        FROM UVW_DATLAB WHERE 
                        Id_cia = '".$datos["p_idCompania"]."' AND 
                        Id_Loc = '".$datos["p_idLocalidad"]."' AND
                        DNI = '".$datos["p_dni"]."'");
            }elseif(isset($datos["p_edad"])){
                /* PARA CRONOGRAMA DE EDAD */
                $stmt = $db->prepare("
                        SELECT convert(varchar(50), Fecha_Ingreso, 21) AS fechaingreso, 
                        (DATEDIFF(YEAR, Fecha_Nacimiento, GETDATE()) + 
                        CASE WHEN (MONTH(GETDATE()) < MONTH(Fecha_Nacimiento) OR 
                        (MONTH(GETDATE()) = MONTH(Fecha_Nacimiento) AND  
                        DAY(GETDATE()) < DAY(Fecha_Nacimiento)))  THEN -1 ELSE 0 END) AS edad, * FROM UVW_DATLAB WHERE 
                        Id_cia = '".$datos["p_idCompania"]."' AND Id_Loc = '".$datos["p_idLocalidad"]."' AND 
                        (DATEDIFF(YEAR, Fecha_Nacimiento, GETDATE()) + 
                        CASE WHEN (MONTH(GETDATE()) < MONTH(Fecha_Nacimiento) OR 
                        (MONTH(GETDATE()) = MONTH(Fecha_Nacimiento) AND  
                        DAY(GETDATE()) < DAY(Fecha_Nacimiento)))  THEN -1 ELSE 0 END) >= '".$datos["p_edad"]."'");
            }elseif(isset($datos["p_tipo"])){
                /* PARA CRONOGRAMA MASIVO */
                $stmt = $db->prepare("
                        SELECT 
                            convert(varchar(50), Fecha_Ingreso, 21) AS fechaingreso,
                            convert(varchar(50), Fe_Ini_Vac, 21) AS Fe_Ini_Vacs,
                            convert(varchar(50), Fe_Fin_Vac, 21) AS Fe_Fin_Vacs,
                            * 
                        FROM 
                            UVW_DATLAB 
                        WHERE
                            Id_cia = '".$datos["p_idCompania"]."' AND 
                            Id_Loc = '".$datos["p_idLocalidad"]."' AND
                            ( 
                                ( 
                                    ( 
                                        (
                                            CONVERT(VARCHAR(4),YEAR('".$datos["p_fechainicio2"]."'), 103)+
                                            RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(Fecha_Ingreso), 103), 2)+
                                            RIGHT( '00' + CONVERT(VARCHAR(2),DAY(Fecha_Ingreso) , 103), 2)
                                        )
                                        BETWEEN  
                                            '".$datos["p_fechainicio1"]."' AND '".$datos["p_fechafin1"]."'
                                    )
                                    OR
                                    ( 
                                        (
                                            CONVERT(VARCHAR(4),YEAR('".$datos["p_fechafin2"]."'), 103)+
                                            RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(Fecha_Ingreso), 103), 2)+
                                            RIGHT( '00' + CONVERT(VARCHAR(2),DAY(Fecha_Ingreso) , 103), 2)
                                        )
                                        BETWEEN  
                                            '".$datos["p_fechainicio1"]."' AND '".$datos["p_fechafin1"]."'
                                    )
                                ) 
                                OR
                                (
                                    (
                                        CONVERT(VARCHAR(4),YEAR(Fe_Ini_Vac), 103)+
                                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(Fe_Ini_Vac), 103), 2)+
                                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(Fe_Ini_Vac) , 103), 2)
                                    ) >= ".$datos["p_fechainicio1"]."
                                )
                            )
                        ORDER BY
                            Fe_Ini_Vac DESC,
                            MONTH(Fe_Ini_Vac) ASC,
                            DAY(Fe_Ini_Vac) ASC,
                            MONTH(Fecha_Ingreso) ASC,
                            DAY(Fecha_Ingreso) ASC
                        ");
            }else{
                /* PARA USUARIO */
                $select = $db->select()->from( array( 'a' => 'UVW_DATLAB' ), 
                        array('Id_cia', 'Id_Loc', 'Ap_Paterno', 'Ap_Materno', 'Nombres', 'DNI', 'email', '(convert(varchar(50), Fecha_Nacimiento, 21)) AS fechanac', 
                              'Sexo', 'Puesto', '(convert(varchar(50), Fecha_Ingreso, 21)) AS fechaini', 'dept_nac', 'prov_nac', 'dist_nac',
                              'Telefono', 'Grado_Instruccion', 'Estado_Civil', 'tipotrab', 'id_Area', 'centrocosto', 'ind_contrata', 'emp_espec') );
                if(isset($datos["p_idCompania"])) $select->where('a.Id_cia = ?', $datos["p_idCompania"]);
                if(isset($datos["p_idLocalidad"])) $select->where('a.Id_Loc = ?', $datos["p_idLocalidad"]);
                if(isset($datos["p_opcionaux"]) && $datos["p_opcionaux"] == "autocomrem"){ 
                    $select->where("a.Ap_Paterno+' '+a.Ap_Materno like '%".$datos["p_nombres"]."%'");
                    $select->order("a.Ap_Paterno ASC");
                }
                $stmt = $db->prepare($select->assemble());
            }
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
}
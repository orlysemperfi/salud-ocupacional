<?php
class Models_Evaluacion extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
        $this->connectMSSQLs();
    }
    
    public function paginado( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT * FROM view_cita AS a WHERE
                    a.estado <> 5 AND
                    a.estado = 6 AND 
                    a.idCompania = '".$datos["p_idCompania"]."' AND 
                    a.idLocalidad = '".$datos["p_idLocalidad"]."'
                    ".(isset($datos["p_motivo"])?"AND a.motivo = ".$datos["p_motivo"]:"")." 
                ORDER BY a.idCita DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function empleadolistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT 
                a.*, b.*, c.*
                FROM view_cita AS a
                INNER JOIN view_empleado AS b ON a.idEmpleado = b.idEmpleado
                INNER JOIN view_cronograma AS c ON a.idCronograma = c.idCronograma
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function puestoautocompletar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            $sql = "
                SELECT DISTINCT a.Puesto FROM UVW_DATLAB AS a
                WHERE a.Puesto LIKE '%".$datos["p_puesto"]."%' ORDER BY a.Puesto ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function enfermedadesautocompletar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.nombre, a.idObservaciones FROM view_enfermedades AS a
                WHERE a.nombre LIKE '%".$datos["p_nombre"]."%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function historiaocupacionallistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, a.nregistro, a.puesto, a.direccion, b.*
                FROM view_cita AS a
                INNER JOIN view_historia AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function historiaocupacionalgrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_historiaocupacional_grabar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"],             PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_nregistro"],          PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_direccion"],          PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_idHistoria"],         PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_fechainicio"],        PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_empresa"],            PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_altitud"],            PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_actividadempresa"],   PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_areatrabajo"],        PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_ocupacion"],         PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_ttsubsuelo"],        PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_ttsuperficie"],      PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_pelageocupacional"], PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_usotipoepp"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(15, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(16, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function fichasieteccomprobar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT idHistoria
                FROM view_historia AS a
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $array;
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function fichaanexo7clistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_fichasietec AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function fichasietecgrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_fichasietec_grabar ?,?,?,?,?,?,?,?,?,?
                                                            ,?,?,?,?,?,?,?,?,?,?
                                                            ,?,?,?,?,?,?,?,?,?,?
                                                            ,?,?,?,?,?,?,?,?,?,?
                                                            ,?,?,?,?,?,?,?,?,?,?
                                                            ,?,?,?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"],                     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idFichasietec"],              PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_arealabor"],                  PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_alturalabor"],                PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_gradoinstruccion"],           PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_otros"],                      PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_reubicacion"],                PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_reinserccion"],               PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_habtabaco"],                  PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_habalcohol"],                PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_habdrogas"],                 PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_puestopostula"],             PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_puestoactual"],              PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_antecedentesocupacionales"], PDO::PARAM_STR);
            $stmt->bindParam(15, $datos["p_antecedentespersonales"],    PDO::PARAM_STR);
            $stmt->bindParam(16, $datos["p_antecedentesfamiliares"],    PDO::PARAM_STR);
            $stmt->bindParam(17, $datos["p_nhijosvivos"],               PDO::PARAM_STR);
            $stmt->bindParam(18, $datos["p_nhijosmuertos"],             PDO::PARAM_STR);
            
            $stmt->bindParam(19, $datos["p_inmunizaciones"],            PDO::PARAM_STR);
            $stmt->bindParam(20, $datos["p_evacabeza"],                 PDO::PARAM_STR);
            $stmt->bindParam(21, $datos["p_evanariz"],                  PDO::PARAM_STR);
            $stmt->bindParam(22, $datos["p_evaboca"],                   PDO::PARAM_STR);
            $stmt->bindParam(23, $datos["p_evaojos"],                   PDO::PARAM_STR);
            $stmt->bindParam(24, $datos["p_evaenferoculares"],          PDO::PARAM_STR);
            $stmt->bindParam(25, $datos["p_evareflejoculares"],         PDO::PARAM_STR);
            $stmt->bindParam(26, $datos["p_evavisioncolores"],          PDO::PARAM_STR);
            $stmt->bindParam(27, $datos["p_evaotoscopiaderecho"],       PDO::PARAM_STR);
            $stmt->bindParam(28, $datos["p_evaotoscopiaizquierdo"],     PDO::PARAM_STR);
            $stmt->bindParam(29, $datos["p_evapulmonesflg"],            PDO::PARAM_INT);
            $stmt->bindParam(30, $datos["p_evapulmonesdescr"],          PDO::PARAM_STR);
            $stmt->bindParam(31, $datos["p_evamiembrossup"],            PDO::PARAM_STR);
            $stmt->bindParam(32, $datos["p_evamiembrosinf"],            PDO::PARAM_STR);
            $stmt->bindParam(33, $datos["p_evareflejososteo"],          PDO::PARAM_STR);
            $stmt->bindParam(34, $datos["p_evamarcha"],                 PDO::PARAM_STR);
            $stmt->bindParam(35, $datos["p_evacolumnavertebral"],       PDO::PARAM_STR);
            $stmt->bindParam(36, $datos["p_evaabdomen"],                PDO::PARAM_STR);
            $stmt->bindParam(37, $datos["p_evatactorectal"],            PDO::PARAM_INT);
            $stmt->bindParam(38, $datos["p_evaanillosinguinales"],      PDO::PARAM_STR);
            $stmt->bindParam(39, $datos["p_evahernias"],                PDO::PARAM_STR);
            $stmt->bindParam(40, $datos["p_evavarices"],                PDO::PARAM_STR);
            $stmt->bindParam(41, $datos["p_evaorganosgenitales"],       PDO::PARAM_STR);
            $stmt->bindParam(42, $datos["p_evaganglios"],               PDO::PARAM_STR);
            $stmt->bindParam(43, $datos["p_evalenguaje"],               PDO::PARAM_STR);
            $stmt->bindParam(44, $datos["p_evavertices"],               PDO::PARAM_STR);
            $stmt->bindParam(45, $datos["p_evacampospulmonares"],       PDO::PARAM_STR);
            $stmt->bindParam(46, $datos["p_evahilios"],                 PDO::PARAM_STR);
            $stmt->bindParam(47, $datos["p_evasenos"],                  PDO::PARAM_STR);
            $stmt->bindParam(48, $datos["p_evamediastino"],             PDO::PARAM_STR);
            $stmt->bindParam(49, $datos["p_evasiluetacardiaca"],        PDO::PARAM_STR);
            $stmt->bindParam(50, $datos["p_evaconclusionesradiograficas"],PDO::PARAM_STR);
            $stmt->bindParam(51, $datos["p_evacalidad"],                PDO::PARAM_STR);
            $stmt->bindParam(52, $datos["p_evasimbolos"],               PDO::PARAM_STR);
            $stmt->bindParam(53, $datos["p_evaradioneumoflg"],          PDO::PARAM_INT);
            $stmt->bindParam(54, $datos["p_evaradiodescr"],             PDO::PARAM_STR);
            $stmt->bindParam(55, $datos["p_evaotrosexamenes"],          PDO::PARAM_STR);
            $stmt->bindParam(56, $datos["p_evaaptotrabajar"],           PDO::PARAM_INT);
            $stmt->bindParam(57, $datos["p_evaobservaciones"],          PDO::PARAM_STR);
            $stmt->bindParam(58, $datos["p_evarecomendaciones"],        PDO::PARAM_STR);
            $stmt->bindParam(59, $datos["p_secretaria"],                PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(60, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(61, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function fichasietedcomprobar( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT idFichasietec
                FROM view_fichasietec AS a
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $array;
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function fichaanexo7dlistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_fichasieted AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function fichasietedgrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_fichasieted_grabar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idFichasieted"],  PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_opc1"],   PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_opc2"],   PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_opc3"],   PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_opc4"],   PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_opc5"],   PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_opc6"],   PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_opc7"],   PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_opc8"],  PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_opc9"],  PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_opc10"], PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_opc11"], PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_opc12"], PDO::PARAM_STR);
            $stmt->bindParam(15, $datos["p_opc13"], PDO::PARAM_STR);
            $stmt->bindParam(16, $datos["p_opc14"], PDO::PARAM_STR);
            $stmt->bindParam(17, $datos["p_opc15"], PDO::PARAM_STR);
            $stmt->bindParam(18, $datos["p_opc16"], PDO::PARAM_STR);
            $stmt->bindParam(19, $datos["p_opc17"], PDO::PARAM_STR);
            $stmt->bindParam(20, $datos["p_apto"],                  PDO::PARAM_STR);
            $stmt->bindParam(21, $datos["p_frecuenciacardiaca"],    PDO::PARAM_STR);
            $stmt->bindParam(22, $datos["p_presionarteriala"],      PDO::PARAM_STR);
            $stmt->bindParam(23, $datos["p_presionarterialb"],      PDO::PARAM_STR);
            $stmt->bindParam(24, $datos["p_frecuenciarespiratoria"],PDO::PARAM_STR);
            $stmt->bindParam(25, $datos["p_imc"],                   PDO::PARAM_STR);
            $stmt->bindParam(26, $datos["p_sat"],                   PDO::PARAM_STR);
            $stmt->bindParam(27, $datos["p_observaciones"],         PDO::PARAM_STR);            
            $stmt->bindParam(28, $datos["p_temperatura"],   PDO::PARAM_STR);
            $stmt->bindParam(29, $datos["p_talla"],         PDO::PARAM_STR);
            $stmt->bindParam(30, $datos["p_peso"],          PDO::PARAM_STR);
            $stmt->bindParam(31, $datos["p_cintura"],       PDO::PARAM_STR);
            $stmt->bindParam(32, $datos["p_cadera"],        PDO::PARAM_STR);
            $stmt->bindParam(33, $datos["p_icc"],           PDO::PARAM_STR);
            $stmt->bindParam(34, $datos["p_opcdesc1"],      PDO::PARAM_STR);
            $stmt->bindParam(35, $datos["p_opcdesc2"],      PDO::PARAM_STR);
            $stmt->bindParam(36, $datos["p_opcdesc3"],      PDO::PARAM_STR);
            $stmt->bindParam(37, $datos["p_opcdesc4"],      PDO::PARAM_STR);
            $stmt->bindParam(38, $datos["p_opcdesc5"],      PDO::PARAM_STR);
            $stmt->bindParam(39, $datos["p_opcdesc6"],      PDO::PARAM_STR);
            $stmt->bindParam(40, $datos["p_opcdesc7"],      PDO::PARAM_STR);
            $stmt->bindParam(41, $datos["p_opcdesc8"],      PDO::PARAM_STR);
            $stmt->bindParam(42, $datos["p_opcdesc9"],      PDO::PARAM_STR);
            $stmt->bindParam(43, $datos["p_opcdesc10"],     PDO::PARAM_STR);
            $stmt->bindParam(44, $datos["p_opcdesc11"],     PDO::PARAM_STR);
            $stmt->bindParam(45, $datos["p_opcdesc12"],     PDO::PARAM_STR);
            $stmt->bindParam(46, $datos["p_opcdesc13"],     PDO::PARAM_STR);
            $stmt->bindParam(47, $datos["p_opcdesc14"],     PDO::PARAM_STR);
            $stmt->bindParam(48, $datos["p_opcdesc15"],     PDO::PARAM_STR);
            $stmt->bindParam(49, $datos["p_opcdesc16"],     PDO::PARAM_STR);
            $stmt->bindParam(50, $datos["p_opcdesc17"],     PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(51, $idUA,                     PDO::PARAM_STR);
            $stmt->bindParam(52, $data,                     PDO::PARAM_STR);
            $stmt->bindParam(53, $datos["p_direccion"],     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function examenescomprobar( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT idFichasieted
                FROM view_fichasieted AS a
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $array;
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function examenescomprobaree( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT 
                (SELECT idCita FROM view_audiometria WHERE idCita = '".$datos["p_idCita"]."') AS a,
                (SELECT idCita FROM view_espirometria WHERE idCita = '".$datos["p_idCita"]."') AS b,
                (SELECT idCita FROM view_optometria WHERE idCita = '".$datos["p_idCita"]."') AS c";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $array;
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function rayosxlistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_rayox AS b ON a.idCita = b.idCita
                WHERE";
            if(isset($datos["p_idCita"]) && !isset($datos["p_nregistro"])) $sql .= " a.idCita = '".$datos["p_idCita"]."'";
            if(isset($datos["p_nregistro"])) $sql .= " b.nregistro = '".$datos["p_nregistro"]."' AND b.idRayosx <> ".$datos["p_idRayosx"];
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function rayosxgrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_rayox_grabar ?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idRayosx"],   PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_nregistro"],  PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_fecha"],      PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(5, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(6, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function laboratoriolistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_laboratorio AS b ON a.idCita = b.idCita
                WHERE  a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function laboratoriograbar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_laboratorio_grabar ?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?,?,?,
                                                             ?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],             PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idLaboratorio"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_gruposanguineo"],     PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_factorsanguineo"],    PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_hemoglobina"],        PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_rpr"],                PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_subunidadbeta"],      PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_fur"],                PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_hematocrito"],        PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_hematies"],          PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_leucocitos"],        PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_juveniles"],         PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_abastonados"],       PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_segmentados"],       PDO::PARAM_STR);
            $stmt->bindParam(15, $datos["p_linfocitos"],        PDO::PARAM_STR);
            $stmt->bindParam(16, $datos["p_monocitos"],         PDO::PARAM_STR);
            $stmt->bindParam(17, $datos["p_eosinofilos"],       PDO::PARAM_STR);
            $stmt->bindParam(18, $datos["p_basofilos"],         PDO::PARAM_STR);
            $stmt->bindParam(19, $datos["p_plaquetas"],         PDO::PARAM_STR);
            $stmt->bindParam(20, $datos["p_comentario"],        PDO::PARAM_STR);
            $stmt->bindParam(21, $datos["p_color"],             PDO::PARAM_STR);
            $stmt->bindParam(22, $datos["p_aspecto"],           PDO::PARAM_STR);
            $stmt->bindParam(23, $datos["p_sedleucocitos"],     PDO::PARAM_STR);
            $stmt->bindParam(24, $datos["p_reaccion"],          PDO::PARAM_STR);
            $stmt->bindParam(25, $datos["p_celulasepiteliales"],PDO::PARAM_STR);
            $stmt->bindParam(26, $datos["p_densidad"],          PDO::PARAM_STR);
            $stmt->bindParam(27, $datos["p_sedhematies"],       PDO::PARAM_STR);
            $stmt->bindParam(28, $datos["p_cristales"],         PDO::PARAM_STR);
            $stmt->bindParam(29, $datos["p_glucosa"],           PDO::PARAM_STR);
            $stmt->bindParam(30, $datos["p_cilindros"],         PDO::PARAM_STR);
            $stmt->bindParam(31, $datos["p_proteinas"],         PDO::PARAM_STR);
            $stmt->bindParam(32, $datos["p_otros"],             PDO::PARAM_STR);
            $stmt->bindParam(33, $datos["p_cetonas"],           PDO::PARAM_STR);
            $stmt->bindParam(34, $datos["p_bilirrubina"],       PDO::PARAM_STR);
            $stmt->bindParam(35, $datos["p_urobilinogeno"],     PDO::PARAM_STR);
            $stmt->bindParam(36, $datos["p_nitritos"],          PDO::PARAM_STR);
            $stmt->bindParam(37, $datos["p_sangre"],            PDO::PARAM_STR);
            $stmt->bindParam(38, $datos["p_colesteroltotal"],   PDO::PARAM_STR);
            $stmt->bindParam(39, $datos["p_hdl"],               PDO::PARAM_STR);
            $stmt->bindParam(40, $datos["p_trigliceridos"],     PDO::PARAM_STR);
            $stmt->bindParam(41, $datos["p_proteinastotales"],  PDO::PARAM_STR);
            $stmt->bindParam(42, $datos["p_albumina"],          PDO::PARAM_STR);
            $stmt->bindParam(43, $datos["p_globulinas"],        PDO::PARAM_STR);
            $stmt->bindParam(44, $datos["p_acidourico"],        PDO::PARAM_STR);
            $stmt->bindParam(45, $datos["p_bioglucosa"],        PDO::PARAM_STR);
            $stmt->bindParam(46, $datos["p_urea"],              PDO::PARAM_STR);
            $stmt->bindParam(47, $datos["p_creatinina"],        PDO::PARAM_STR);
            $stmt->bindParam(48, $datos["p_amilasa"],           PDO::PARAM_STR);
            $stmt->bindParam(49, $datos["p_tgo"],               PDO::PARAM_STR);
            $stmt->bindParam(50, $datos["p_tgp"],               PDO::PARAM_STR);
            $stmt->bindParam(51, $datos["p_ggt"],               PDO::PARAM_STR);
            $stmt->bindParam(52, $datos["p_fosfatasaalcalina"], PDO::PARAM_STR);
            $stmt->bindParam(53, $datos["p_bilirrubinatotal"],  PDO::PARAM_STR);
            $stmt->bindParam(54, $datos["p_bilirrubinadirecta"],PDO::PARAM_STR);
            $stmt->bindParam(55, $datos["p_bilirrubinaindirecta"],PDO::PARAM_STR);
            $stmt->bindParam(56, $datos["p_biocomentario"],     PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(57, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(58, $data,                     PDO::PARAM_STR);
            
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function audiometrialistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_audiometria AS b ON a.idCita = b.idCita
                WHERE  a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function audiometriagrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_audiometria_grabar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idAudiometria"],  PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_ad1"],            PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_ad2"],            PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_ad3"],            PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_ad4"],            PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_ad5"],            PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_ad6"],            PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_ad7"],            PDO::PARAM_STR);
            
            $stmt->bindParam(10, $datos["p_ai1"],           PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_ai2"],           PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_ai3"],           PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_ai4"],           PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_ai5"],           PDO::PARAM_STR);
            $stmt->bindParam(15, $datos["p_ai6"],           PDO::PARAM_STR);
            $stmt->bindParam(16, $datos["p_ai7"],           PDO::PARAM_STR);
            $stmt->bindParam(17, $datos["p_comentario"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(18, $idUA,                     PDO::PARAM_STR);
            $stmt->bindParam(19, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function espirometrialistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_espirometria AS b ON a.idCita = b.idCita
                WHERE  a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function espirometriagrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_espirometria_grabar ?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idEspirometria"], PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_espirometria"],   PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_edadpulmonar"],   PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_fev"],            PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_fvc"],            PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_fevfvc"],         PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_fev2575"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(9, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(10, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function optometrialistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_optometria AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function optometriagrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_optometria_grabar ?,?,?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idOptometria"],   PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_ovcscod"],        PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_ovlscod"],        PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_ovcscoi"],        PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_ovlscoi"],        PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_ovccod"],         PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_ovlcod"],         PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_ovccoi"],         PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_ovlcoi"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(11, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(12, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function odontogramalistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_odontograma AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function odontogramagrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_odontograma_grabar ?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idOdontograma"],  PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_piezacompleta"],  PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_piezaextraida"],  PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_piezamal"],       PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_observaciones"],  PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_grafico"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(8, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(9, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function trabajoalturalistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_trabajoaltura AS b ON a.idCita = b.idCita
                WHERE  a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function trabajoalturagrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_trabajoaltura_grabar ?,?,?,?,?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],             PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idTrabajoaltura"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_habcoca"],            PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_habalcohol"],         PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_habtabaco"],          PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_habdrogas"],          PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_fobaltura"],          PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_foblugcer"],          PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_fobespcon"],          PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_epilepsia"],         PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_viscerder"],         PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_viscerizq"],         PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_vislejder"],         PDO::PARAM_STR);
            $stmt->bindParam(14, $datos["p_vislejizq"],         PDO::PARAM_STR);
            $stmt->bindParam(15, $datos["p_vertigo1"],          PDO::PARAM_STR);
            $stmt->bindParam(16, $datos["p_vertigo2"],          PDO::PARAM_STR);
            $stmt->bindParam(17, $datos["p_vertigo3"],          PDO::PARAM_STR);
            $stmt->bindParam(18, $datos["p_vertigo4"],          PDO::PARAM_STR);
            $stmt->bindParam(19, $datos["p_vertigo5"],          PDO::PARAM_STR);
            $stmt->bindParam(20, $datos["p_vertigo6"],          PDO::PARAM_STR);
            $stmt->bindParam(21, $datos["p_asma1"],             PDO::PARAM_STR);
            $stmt->bindParam(22, $datos["p_asma2"],             PDO::PARAM_STR);
            $stmt->bindParam(23, $datos["p_asma3"],             PDO::PARAM_STR);
            $stmt->bindParam(24, $datos["p_asma4"],             PDO::PARAM_STR);
            $stmt->bindParam(25, $datos["p_asma5"],             PDO::PARAM_STR);
            $stmt->bindParam(26, $datos["p_asma6"],             PDO::PARAM_STR);
            $stmt->bindParam(27, $datos["p_evacard1"],          PDO::PARAM_STR);
            $stmt->bindParam(28, $datos["p_evacard2"],          PDO::PARAM_STR);
            $stmt->bindParam(29, $datos["p_evacard3"],          PDO::PARAM_STR);
            $stmt->bindParam(30, $datos["p_evacard4"],          PDO::PARAM_STR);
            $stmt->bindParam(31, $datos["p_evacard5"],          PDO::PARAM_STR);
            $stmt->bindParam(32, $datos["p_evacard6"],          PDO::PARAM_STR);
            $stmt->bindParam(33, $datos["p_evacard7"],          PDO::PARAM_STR);
            $stmt->bindParam(34, $datos["p_evacard8"],          PDO::PARAM_STR);
            $stmt->bindParam(35, $datos["p_evacard9"],          PDO::PARAM_STR);
            $stmt->bindParam(36, $datos["p_indicemasa"],        PDO::PARAM_STR);
            $stmt->bindParam(37, $datos["p_sisloc1"],           PDO::PARAM_STR);
            $stmt->bindParam(38, $datos["p_sisloc2"],           PDO::PARAM_STR);
            $stmt->bindParam(39, $datos["p_sisloc3"],           PDO::PARAM_STR);
            $stmt->bindParam(40, $datos["p_sisloc4"],           PDO::PARAM_STR);
            $stmt->bindParam(41, $datos["p_sisloc5"],           PDO::PARAM_STR);
            $stmt->bindParam(42, $datos["p_sisloc6"],           PDO::PARAM_STR);
            $stmt->bindParam(43, $datos["p_evapsi1"],           PDO::PARAM_STR);
            $stmt->bindParam(44, $datos["p_evapsi2"],           PDO::PARAM_STR);
            $stmt->bindParam(45, $datos["p_evapsi3"],           PDO::PARAM_STR);
            $stmt->bindParam(46, $datos["p_evapsi4"],           PDO::PARAM_STR);
            $stmt->bindParam(47, $datos["p_evapsi5"],           PDO::PARAM_STR);
            $stmt->bindParam(48, $datos["p_evapsi6"],           PDO::PARAM_STR);
            $stmt->bindParam(49, $datos["p_evapsi7"],           PDO::PARAM_STR);
            $stmt->bindParam(50, $datos["p_observaciones"],     PDO::PARAM_STR);
            $stmt->bindParam(51, $datos["p_apto"],              PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(52, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(53, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function pasevisitantelistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_pasevisitante AS b ON a.idCita = b.idCita
                WHERE  a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function pasevisitantegrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_pasevisitante_grabar ?,?,?,?,?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],             PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idPasevisitante"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_pregunta1"],          PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_pregunta2"],          PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_pregunta3"],          PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_pregunta4"],          PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_pregunta5"],          PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_pregunta6"],          PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_pregunta7"],          PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_pregunta8"],         PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_pregunta9"],         PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_pregunta10"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(13, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(14, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function resultadoeelistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT a.idCita, b.*
                FROM view_cita AS a
                INNER JOIN view_equipopesado AS b ON a.idCita = b.idCita
                WHERE a.idCita = '".$datos["p_idCita"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function resultadoeegrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_resultadoee_grabar ?,?,?,?,?,?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"],         PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idEquipopesado"], PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_para"],           PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_tipoexamen"],     PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_resespirometria"],PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_resaudiometria"], PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_resoptometria"],  PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_apto"],           PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(9, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(10, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function citaterminar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cita_terminar ?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(2, $idUA,  PDO::PARAM_STR);
            $stmt->bindParam(3, $data,  PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function archivonombregrabar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_archivonombre_grabar ?,?,?,?,?");

            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_tipo"],   PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_nombre"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(4, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(5, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function archivonombrelistar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT
                    (SELECT documento FROM view_audiometria WHERE idCita = ".$datos["p_idCita"].") AS d2,
                    (SELECT documento FROM view_espirometria WHERE idCita = ".$datos["p_idCita"].") AS d3,
                    (SELECT documento FROM view_cita WHERE idCita = ".$datos["p_idCita"].") AS d1
                ";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function examencomprobar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "
                SELECT
                    (SELECT COUNT(idCita) FROM view_historia        WHERE idCita = ".$datos["p_idCita"].") AS d1,
                    (SELECT COUNT(idCita) FROM view_fichasietec     WHERE idCita = ".$datos["p_idCita"].") AS d2,
                    (SELECT COUNT(idCita) FROM view_fichasieted     WHERE idCita = ".$datos["p_idCita"].") AS d3,
                    (SELECT COUNT(idCita) FROM view_rayox           WHERE idCita = ".$datos["p_idCita"].") AS d4,
                    (SELECT COUNT(idCita) FROM view_laboratorio     WHERE idCita = ".$datos["p_idCita"].") AS d5,
                    (SELECT COUNT(idCita) FROM view_audiometria     WHERE idCita = ".$datos["p_idCita"].") AS d6,
                    (SELECT COUNT(idCita) FROM view_espirometria    WHERE idCita = ".$datos["p_idCita"].") AS d7,
                    (SELECT COUNT(idCita) FROM view_odontograma     WHERE idCita = ".$datos["p_idCita"].") AS d8,
                    (SELECT COUNT(idCita) FROM view_pasevisitante   WHERE idCita = ".$datos["p_idCita"].") AS d9,
                    (SELECT COUNT(idCita) FROM view_trabajoaltura   WHERE idCita = ".$datos["p_idCita"].") AS d10,
                    (SELECT COUNT(idCita) FROM view_optometria      WHERE idCita = ".$datos["p_idCita"].") AS d11,
                    (SELECT COUNT(idCita) FROM view_equipopesado    WHERE idCita = ".$datos["p_idCita"].") AS d12
                ";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
}
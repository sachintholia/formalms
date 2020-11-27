<?php

class AggregatedcertificateAlms extends Model
{
    protected $db;

    public function __construct(){

        $this->db = DbConn::getInstance();

    }

    
    
    /**
     *
     * Query for counting all metacertificates in platform
     *
     * @return int The number of metacertificates
     *
     */
    public function getCountMetacertificates($ini){

        $query_certificate_tot = "SELECT COUNT(*) AS 'tot_metacertificates' FROM ".$GLOBALS['prefix_lms']."_certificate";

        if (isset($_POST['filter']))
        {
            if ($_POST['filter_text'] !== '')
                $query_certificate .=	" AND ( name LIKE '%".$_POST['filter_text']."%'"
                    ." OR code LIKE '%".$_POST['filter_text']."%' )";
        }
        $query_certificate .= " ORDER BY id_certificate
	    LIMIT $ini,".Get::sett('visuItem');


        $row = sql_fetch_array(sql_query($query_certificate_tot));

        return $row['tot_metacertificates'];


    }

    public function getAllMetacertificates() {
        //search query of certificates
        $query_certificate = "
	        SELECT id_certificate, code, name, description
	        FROM ".$GLOBALS['prefix_lms']."_certificate"
            ." WHERE meta = 1";

        $rs = sql_query($query_certificate);
        while($rows = sql_fetch_assoc($rs)) {
            $arr_metacert[] = $rows;
        }
        return $arr_metacert;
    }
    
    
    

       public function getCoursesFromIdMeta($id_meta){
      
       //Take courses for the meta certificate
       $query =    "SELECT DISTINCT idCourse"
                ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                ." WHERE idMetaCertificate = '".$id_meta."'";
                    
       $rs = sql_query($query);

       while($rows = sql_fetch_assoc($rs)) {
            $idCoursesArr[] = $rows['idCourse'];
       }
       return $idCoursesArr;
    } 
    
    
    public function getIdStFromidUser($users){
      
       $query =    "SELECT idst"
                ." FROM ".$GLOBALS['prefix_fw']."_user"
                ." WHERE idst IN (".implode(',',$users).")"
                ." ORDER BY userid";
                    
       $rs = sql_query($query);
        
       while($rows = sql_fetch_assoc($rs)) {
            $idstArr[] = $rows['idst'];
       }
       return $idstArr;
    }       
     
    public function getUsersFromIdMeta($id_meta){
      
        $query =    "SELECT DISTINCT idUser"
                    ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                    ." WHERE idMetaCertificate = '".$id_meta."'";
                    
        $rs = sql_query($query);
        
        while($rows = sql_fetch_assoc($rs)) {
            $id_users[] = $rows['idUser'];
        }
        return $id_users;
    }

    
   
    public function getIdsMetaCertificate($idCert) { // get all associations.
        
        $query =    "SELECT idMetaCertificate"
                    ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
                    ." WHERE idCertificate = '".$idCert."'";
                    
        $rs = sql_query($query);
            while($rows = sql_fetch_assoc($rs)) {
             
                $idsMeta[] = $rows['idMetaCertificate'];
             
            }
        
        return $idsMeta;                    
                    
    }
    
    public function getUsersCourseCompleted(){
        
        $query =    "SELECT idCourse, idUser"
                ." FROM ".$GLOBALS['prefix_lms']."_courseuser"
                ." WHERE status = '"._CUS_END."'";
                
        $rs = sql_query($query);
            while($rows = sql_fetch_assoc($rs)) {
             
                $arr_usersCourseCompleted[] = $rows;
             
            }
        
        return $arr_usersCourseCompleted;        
    }


    public function getTitleAssociationsArr(){
        
       $query =    "SELECT idMetaCertificate, title"
                    ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta";
                
        $rs = sql_query($query);
            while($rows = sql_fetch_assoc($rs)) {
             
                $arr_title[] = $rows;
             
            }
        
        return $arr_title;        
    }
    
     public function getCountMetaCertUsers(){
        
       $query =   "SELECT idUser, idMetaCertificate, COUNT(*)"
                ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                ." GROUP BY idUser, idMetaCertificate";
                
        $rs = sql_query($query);
            while($rows = sql_fetch_assoc($rs)) {
             
                $arr_control[] = $rows;
             
            }
        
        return $arr_control;        
    }
   
    public function getDataUsersInMetaCerts($idsMetacertArr){
            // m = learning_certificate_meta_course
            // u = core_user
            // userid = username
            
        $query =    "SELECT m.idUser, u.lastname, u.firstname, u.userid"
                ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course as m"
                ." JOIN ".$GLOBALS['prefix_fw']."_user as u ON u.idst = m.idUser"
                ." WHERE m.idMetaCertificate IN (".implode(',', $idsMetacertArr).")"
                .(isset($_POST['filter_username']) ? "AND u.userid LIKE '%".$_POST['filter_username']."%'" : '')
                .(isset($_POST['filter_firstname']) ? "AND u.firstname LIKE '%".$_POST['filter_firstname']."%'" : '')
                .(isset($_POST['filter_lastname']) ? "AND u.lastname LIKE '%".$_POST['filter_lastname']."%'" : '')
                ." GROUP BY m.idUser, u.lastname, u.firstname, u.userid"
                ." ORDER BY u.lastname, u.firstname, u.userid";

                
        $rs = sql_query($query);   
        
        $usersArr = array();
        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {
         
            $usersArr[$i]['idUser'] = $rows['idUser'];
            $usersArr[$i]['lastName'] = $rows['lastname'];
            $usersArr[$i]['firstname'] = $rows['firstname'];
            $usersArr[$i]['username'] = $rows['userid'];
         
            $i++;
        }
        
        return $usersArr;         
    }
    
    
    public function getIdCourseFromIdUserAndIdMeta($idUser,$idMeta){
        
        $query =    "SELECT idCourse"
                            ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                            ." WHERE idUser = '".$idUser."'"
                            ." AND idMetaCertificate = '".$idMeta."'";

        $rs = sql_query($query);
        while($rows = sql_fetch_array($rs)) {
         
            $arrIdCourse[] = $rows['idCourse'];
         
        }
        
        return $arrIdCourse;
        
    }
    
    public function getCertReleased($idUser,$idMeta){
        
        $query =    "SELECT COUNT(*) FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
                    ." WHERE idUser = '".$idUser."'"
                    ." AND idMetaCertificate = '".$idMeta."'";

        $rs = sql_query($query);   

        return sql_fetch_row($rs);
        
    }

    public function insertMetacert($cert_datas) {

        $query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_certificate
		( code, name, base_language, description, meta, user_release ) VALUES
		( 	'".$cert_datas['code']."' ,
			'".$cert_datas['name']."' ,
		 	'".$cert_datas['base_language']."' ,
			'".$cert_datas['descr']."',
			'1',
			'".(isset($cert_datas['user_release']) ? 1 : 0)."'
		)";

        $rs = sql_query($query);

        return $rs;
    }

    public function updateMetacert($cert_datas)
    {
        $query = "
		UPDATE ".$GLOBALS['prefix_lms']."_certificate
		SET	code = '".$cert_datas['code']."',
			name = '".$cert_datas['name']."',
			base_language = '".$cert_datas['base_language']."',
			description = '".$cert_datas['descr']."',
			user_release = '".(isset($cert_datas['user_release']) ? 1 : 0)."'
		WHERE id_certificate = '".$cert_datas["id_certificate"]."'";

        $rs = sql_query($query);

        return $rs;
    }

    


    
    function deleteAssociationsCourses($coursesIdsArr, $idMeta) {   
        
        $query2 = "DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
        . " WHERE idMetaCertificate = " . $idMeta
        . " AND idCourse IN (" . implode(",", $coursesIdsArr) . ")";
        
        $rs = sql_query($query2);
        return $rs;
        
    }
    
    public function getPathsFromIdParent($idParent)
    {
        $q = "SELECT path,idCategory,lev, iLeft, iRight FROM ". $GLOBALS["prefix_lms"] ."_category"
            . " WHERE idParent = ". $idParent ."";

        $rs = sql_query($q);
        $i = 0;
        while($rows = sql_fetch_array($rs)){

            $nodesArr[$i]['text'] = $rows['path'];
            $nodesArr[$i]['idCategory'] = $rows['idCategory'];
            $nodesArr[$i]['level'] = $rows['lev'];

            // If the node has no child, the property will be NULL, otherwise will be an array to fill

            $nodesArr[$i]['isLeaf'] = ($rows['iRight'] - $rows['iLeft'] === 1);
           // $nodesArr[$i]['nodes'] = ($rows['iRight'] - $rows['iLeft'] === 1) ? NULL : array();

            $i++;
        }
        return $nodesArr;
    }

    public function getNodesFromIdParent($idParent)
    {
        $q = "SELECT path,idCategory,lev, iLeft, iRight FROM ". $GLOBALS["prefix_lms"] ."_category"
            . " WHERE idParent = ". $idParent ."";

        $rs = sql_query($q);

        $nodesArr = array();

        $i = 0;
        while($rows = sql_fetch_array($rs)){

            $nodesArr[$i]['path'] = $rows['path'];
            $nodesArr[$i]['idCategory'] = $rows['idCategory'];
            $nodesArr[$i]['level'] = $rows['lev'];


            // If the node has no child, the property will be NULL, otherwise will be an array to fill
            $nodesArr[$i]['isLeaf'] = ($rows['iRight'] - $rows['iLeft'] === 1);

            $i++;
        }
        return $nodesArr;
    }


    


    

    
     public function getCatalogCourse() {
        
        $q = " SELECT idCatalogue, name, description
             FROM ".$GLOBALS['prefix_lms']."_catalogue ";
        $rs = sql_query($q);
              
        $catalogCourseArr['data'] = []; 
        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $catalogCourseArr['data'][$i]["idCatalogue"]    = $rows["idCatalogue"];
            $catalogCourseArr['data'][$i]["nameCatalog"]  = $rows["name"];
            $catalogCourseArr['data'][$i]["descriptionCatalog"]  = $rows["descr"];   
            $i += 1;
        }
        $catalogCourseArr["recordsTotal"] = count($catalogCourseArr['data']);
        $catalogCourseArr["recordsFiltered"] = count($catalogCourseArr['data']);
        $catalogCourseArr["draw"] = 1;

        
        return $catalogCourseArr;
    }
    
    
    
    function getCertFile($id_user, $id_meta) {
        
        $query =   "SELECT cert_file"
                    ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
                    ." WHERE idUser = '".$id_user."'"
                    ." AND idMetaCertificate = '".$id_meta."'";
        
        
        
        return sql_fetch_row(sql_query($query));
    }
    
    
    function deleteReleasedCert($id_user, $id_meta){
         $query =    "DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
                            ." WHERE idUser = '".$id_user."'"
                            ." AND idMetaCertificate = '".$id_meta."'";
         
         return sql_query($query);
    }
    
    public function insertCertificateMeta($metadataAssocArr){
        
        $q =    "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta (idCertificate, title, description)"
                                    ." VALUES ('".$metadataAssocArr['id_certificate']."', '".addslashes($metadataAssocArr['title'])."', '".addslashes($metadataAssocArr['description'])."')";
 
        return sql_query($q);
 
    }
    
    
    
    public function getAssociationMetadata($id_metacertificate){
        
       $query =    "SELECT title, description"                  
         ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
         ." WHERE idMetaCertificate = ". $id_metacertificate;
                
        $rs = sql_query($query);
            while($rows = sql_fetch_assoc($rs)) {
             
                $assocMetadataArr["title"] = $rows["title"];
                $assocMetadataArr["description"] = $rows["description"];
             
            }
        
        return $assocMetadataArr;        
    }
    
    public function getLastInsertedIdCertificateMeta(){
        
        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID() FROM ".$GLOBALS['prefix_lms']."_certificate_meta"))[0];
        
    }
    
   

    
    function userBelongCourseMeta($idMetaCert, $id_user, $id_course){
        
       $q = "SELECT * FROM "
            . $GLOBALS['prefix_lms'] . "_certificate_meta_course "
            . " WHERE idMetaCertificate = " . $idMetaCert
            . " AND idUser = " . $id_user
            . " AND idCourse = " . $id_course;
            
       return sql_fetch_assoc(sql_query($q));   
        
    } 
    
    function userBelongCoursePathMeta($idMetaCert, $id_user, $id_coursePath){
        
       $q = "SELECT * FROM "
            . $GLOBALS['prefix_lms'] . "_certificate_meta_coursepath "
            . " WHERE idMetaCertificate = " . $idMetaCert
            . " AND idUser = " . $id_user
            . " AND idCourse = " . $id_coursePath;
            
       return sql_fetch_assoc(sql_query($q));   
        
    }   
    
    function getCoursesInAssociationFromUser($idMeta, $idUser, $type_assoc){
        
         switch($type_assoc) {
            case COURSE:
                $table = 'course';
                $field = 'idCourse';
                break;
            case COURSE_PATH:
                $table = 'coursepath';
                $field = 'idCoursePath';

                break;
            default:
                return;
        }
        
        $q = "SELECT " . $field . " FROM "
            . $GLOBALS['prefix_lms'] . "_certificate_meta_" . $table
            . " WHERE idMetaCertificate = " . $idMeta
            . " AND idUser = " . $idUser;
            
        $idsArr = array();  // They can be from courses or coursepath
        $rs = sql_query($q); 
         while($row = sql_fetch_array($rs)){
            
            $idsArr[] = (int) $row[$field];
            
         }
        
        return $idsArr; 
        
    }
    
     function getUserCoursesFromIdsMeta($idUser, $idsMetacertArr, $type_assoc){
        
         switch($type_assoc) {
            case COURSE:
                $table = 'course';
                $field = 'idCourse';
                break;
            case COURSE_PATH:
                $table = 'coursepath';
                $field = 'idCoursePath';

                break;
            default:
                return;
        }
        
        $q = "SELECT " . $field . " FROM "
            . $GLOBALS['prefix_lms'] . "_certificate_meta_" . $table
            . " WHERE idUser = " . $idUser
            . " AND idMetaCertificate IN (" . implode( ', ', $idsMetacertArr ) . ")" ;
            
        $idsArr = array();  // They can be from courses or coursepath
        $rs = sql_query($q); 
         while($row = sql_fetch_array($rs)){
            
            $idsArr[] = (int) $row[$field];
            
         }
        
        return $idsArr; 
        
    }
    
    function getCoursesFromPath($idCoursePath) {
        
         $q = "SELECT id_item FROM "
            . $GLOBALS['prefix_lms'] . "_coursepath_courses "
            . " WHERE id_path = " . $idCoursePath;
            
         $rs = sql_query($q);
         
         $idsCourseArr = array();
         
         while($row = sql_fetch_array($rs)){
            
            $idsCourseArr[] = (int) $row['id_item'];
            
         }
        
        return $idsCourseArr; 
                        
    }
    
    

    
    function getUsersInIdsMetaArr($idsMetaCertArr){
               
        $q = "SELECT DISTINCT idUser "
            . "FROM " 
            . $GLOBALS['prefix_lms'] . "_certificate_meta_course "
            . " WHERE idMetaCertificate IN (".implode(', ',$idsMetaCertArr).")"
            . " UNION "
            . "SELECT DISTINCT idUser "
            . "FROM " 
            . $GLOBALS['prefix_lms'] . "_certificate_meta_coursepath "
            . " WHERE idMetaCertificate IN (".implode(', ',$idsMetaCertArr).")";
        
        $rs = sql_query($q);
        
        $usersArr = array();
         
             while($row = sql_fetch_array($rs)){
                
                $usersArr[] = (int) $row['idUser'];
                
             }
        
        return $usersArr;            
    }

    function getTypeMetacert($id_metacert){
                
        $coursesTypeArr = array(
        COURSE => "course",
        COURSE_PATH => "coursepath"
        );
        
        
        foreach($coursesTypeArr as $key => $table) {
        
            $q = "SELECT * 
                FROM ". $GLOBALS['prefix_lms'] . "_certificate_meta_" . $table ."
                WHERE idMetaCertificate = ".$id_metacert
                ." LIMIT 1";
           
            $rs = sql_query($q); 
         
            if(sql_fetch_array($rs)) {
                return $key;  
            }
            
        } 
                 
    }
}

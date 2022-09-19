<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once dirname(__FILE__) . '/lib.connector.php';

// Constant definition for language

define(LANG_NOT_SET_IN_CSV, '');
define(NOT_VALID_LANG, null);
define(USER_HAS_NO_LANG, null);

/**
 * class for define docebo users connection to data source.
 *
 * @version    1.1
 *
 * @author        Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboUsers extends DoceboConnector
{
    public $last_error = '';

    public $all_cols = ['idst',
        'userid',
        'firstname',
        'lastname',
        'pass',
        'email',
        'avatar',
        'signature',
        'templatename',
        'language',
        'valid',
        'tree_code', ];

    public $mandatory_cols = ['userid'];

    /**
     * Array containing all the default value for inserting rows in db, if there
     *       are any problems or the field retrieved isn't set.
     *
     * @var array default_cols
     */
    public $default_cols = ['firstname' => '',
        'lastname' => '',
        'pass' => '',
        'email' => '',
        'avatar' => '',
        'signature' => '',
        'templatename' => '',
        'language' => '',
        'tree_code' => '', ];

    public $ignore_cols = ['idst',
        'avatar',
        'lastenter',
        'valid',
        'pwd_expire_at',
        'level',
        'register_date', ];

    public $valid_filed_type = ['textfield', 'date', 'dropdown', 'yesno', 'upload', 'freetext', 'country'];

    /**
     * Array of arrays, that contains all the fields that will be added in the UI
     * select html field, in the creation of a task.
     *
     * @var array
     */
    public $cols_descriptor = null;

    public $dbconn = null;
    public $tree = 0;
    public $tree_desc = 0;
    public $groupFilter = 0;

    public $readwrite = 0; // read = 1, write = 2, readwrite = 3
    public $canceled = 1;  // suspend = 1, delete = 2
    public $sendnotify = 1; // send notify = 1, don't send notify = 2

    public $name = '';
    public $description = '';
    public $preg_match_folder = '\[(.*)\]';

    public $directory = null;
    public $tree_view = null;
    /** @var PeopleListView */
    public $people_view = null;

    public $data = false;

    public $tree_oc = null;
    public $tree_ocd = null;

    public $org_chart_name = [];
    public $org_chart_code = [];
    public $org_chart_group = [];
    public $user_org_chart = [];
    public $all_user_updated = [];

    public $arr_idst_inserted = [];

    public $org_chart_destination = 0;

    public $pwd_force_change_policy = 'do_nothing';

    public $inserted_user_org_chart = [];

    /**
     * This constructor require the source file name.
     *
     * @param array $params the array of params
     *                      - 'filename' => name of the file (required)
     *                      - 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
     *                      - 'separator' => string a char with the fields separator (Optional, default = ,)
     **/
    public function DoceboConnectorDoceboUsers($params)
    {
        if ($params === null) {
            return;
        }    // connector
        else {
            $this->set_config($params);
        }    // connection
    }

    public function get_config()
    {
        return ['tree' => $this->tree,
            //'group' => $this->groupFilter,
            'canceled' => $this->canceled,
            'readwrite' => $this->readwrite,
            'sendnotify' => $this->sendnotify,
            'name' => $this->name,
            'description' => $this->description,
            'preg_match_folder' => $this->preg_match_folder,
            'org_chart_destination' => $this->org_chart_destination,
            'pwd_force_change_policy' => $this->pwd_force_change_policy,
        ];
    }

    public function set_config($params)
    {
        if (isset($params['tree'])) {
            $this->tree = $params['tree'];
        }
        //if( isset($params['group']) )		$this->groupFilter = $params['group'];
        if (isset($params['canceled'])) {
            $this->canceled = $params['canceled'];
        }
        if (isset($params['readwrite'])) {
            $this->readwrite = $params['readwrite'];
        }
        if (isset($params['sendnotify'])) {
            $this->sendnotify = $params['sendnotify'];
        }
        if (isset($params['name'])) {
            $this->name = $params['name'];
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['preg_match_folder'])) {
            $this->preg_match_folder = $params['preg_match_folder'];
        }
        if (isset($params['org_chart_destination'])) {
            $this->org_chart_destination = $params['org_chart_destination'];
        }
        if (isset($params['pwd_force_change_policy'])) {
            $this->pwd_force_change_policy = $params['pwd_force_change_policy'];
        }
    }

    public function get_configUI()
    {
        return new DoceboConnectorDoceboUsersUI($this);
    }

    public function connect()
    {
        require_once _base_ . '/lib/lib.userselector.php';
        require_once _adm_ . '/lib/lib.field.php';

        $aclManager = Docebo::user()->getACLManager();

        $this->directory = new UserSelector();
        $this->groupFilter_idst = $aclManager->getGroupST($this->groupFilter);

        // load language for fields names
        $lang_dir = DoceboLanguage::createInstance('admin_directory', 'framework');
        $fl = new FieldList();
        $this->fl = $fl;

        // root and root descendant
        $tmp = $aclManager->getGroup(false, '/oc_0');
        $arr_idst[] = $tmp[0];
        $this->tree_oc = $tmp[0];
        $tmp = $aclManager->getGroup(false, '/ocd_0');
        $arr_idst[] = $tmp[0];
        $this->tree_ocd = $tmp[0];

        // tree folder selected
        if ($this->tree != 0) {
            $arr_groupid = $aclManager->getGroupsId($arr_idst);
            foreach ($arr_groupid as $key => $val) {
                $arr_groupid[$key] = substr_replace($val, '/ocd', 0, 3);
            }
            $arr_result = $aclManager->getArrGroupST($arr_groupid);

            list($this->tree_desc) = array_values($arr_result);
            $arr_idst[] = $this->tree;
            $arr_idst[] = $this->tree_desc;
        }
        $arr_fields = $fl->getFieldsFromIdst($arr_idst);

        // generating cols descriptor
        $this->cols_descriptor = null;
        if ($this->dbconn === null) {
            $this->dbconn = $GLOBALS['dbConn'];
        }
        $query = 'SHOW FIELDS FROM ' . $GLOBALS['prefix_fw'] . '_user';
        $rs = sql_query($query, $this->dbconn);
        if ($rs === false) {
            $this->last_error = Lang::t('_OPERATION_FAILURE', 'standard') . $query . ' [' . sql_error() . ']';

            return false;
        }
        $this->cols_descriptor = [];
        while ($field_info = sql_fetch_array($rs)) {
            if (!in_array($field_info['Field'], $this->ignore_cols)) {
                $mandatory = in_array($field_info['Field'], $this->mandatory_cols);
                if (isset($this->default_cols[$field_info['Field']])) {
                    $this->cols_descriptor[] =
                        [DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_' . $field_info['Field']),
                            DOCEBOIMPORT_COLID => $field_info['Field'],
                            DOCEBOIMPORT_COLMANDATORY => $mandatory,
                            DOCEBOIMPORT_DATATYPE => $field_info['Type'],
                            DOCEBOIMPORT_DEFAULT => $this->default_cols[$field_info['Field']],
                        ];
                } else {
                    $this->cols_descriptor[] =
                        [DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_' . $field_info['Field']),
                            DOCEBOIMPORT_COLID => $field_info['Field'],
                            DOCEBOIMPORT_COLMANDATORY => $mandatory,
                            DOCEBOIMPORT_DATATYPE => $field_info['Type'],
                        ];
                }
            }
        }

        sql_free_result($rs);

        foreach ($arr_fields as $field_id => $field_info) {
            if (in_array($field_info[FIELD_INFO_TYPE], $this->valid_filed_type)) {
                $this->cols_descriptor[] =
                    [DOCEBOIMPORT_COLNAME => $field_info[FIELD_INFO_TRANSLATION],
                        DOCEBOIMPORT_COLID => $field_id,
                        DOCEBOIMPORT_COLMANDATORY => false,
                        DOCEBOIMPORT_DATATYPE => 'text',
                        DOCEBOIMPORT_DEFAULT => false,
                    ];
            }
        }

        //Added tree_code field
        $this->cols_descriptor[] =
            [DOCEBOIMPORT_COLNAME => 'tree_code',
                DOCEBOIMPORT_COLID => 'tree_code',
                DOCEBOIMPORT_COLMANDATORY => false,
                DOCEBOIMPORT_DATATYPE => 'text', ];

        //Added language field
        $this->cols_descriptor[] =
            [DOCEBOIMPORT_COLNAME => Lang::t('_LANGUAGE'),
                DOCEBOIMPORT_COLID => 'language',
                DOCEBOIMPORT_COLMANDATORY => false,
                DOCEBOIMPORT_DATATYPE => 'text',
            ];

        $this->arr_fields = $arr_fields;

        $this->index = 0;
        $this->eof = true;
        $this->org_chart_code = [];
        $match = [];
        $this->org_chart_name = [];
        $this->org_chart_group = [];
        $this->user_org_chart = [];
        // cache org_chart group
        $this->org_chart_group = $aclManager->getBasePathGroupST('/oc');

        if ($this->org_chart_destination == 0) {
            $this->org_chart_code['root'] = 0;
            $idst_group = $this->org_chart_group['/oc_0'];
            $query_idstMember = 'SELECT idstMember'
                . ' FROM ' . $GLOBALS['prefix_fw'] . '_group_members '
                . " WHERE idst = '" . $idst_group . "'";
            $re = sql_query($query_idstMember);
            while (list($idstMember) = sql_fetch_row($re)) {
                if (!isset($this->user_org_chart[$idstMember])) {
                    $this->user_org_chart[$idstMember] = [];
                }
                $this->user_org_chart[$idstMember][0] = 0;
            }

            $query = ' SELECT idOrg, code '
                . ' FROM ' . $GLOBALS['prefix_fw'] . '_org_chart_tree';
            $result = sql_query($query);
            while (list($id_dir, $dir_code) = sql_fetch_row($result)) {
                $valid = preg_match('/' . $this->preg_match_folder . '/i', $dir_code, $match);
                if ($valid) {
                    $dir_code = $match[1];
                }

                $this->org_chart_code[$dir_code] = $id_dir;
                $idst_group = $this->org_chart_group['/oc_' . $id_dir];

                $query_idstMember = 'SELECT idstMember'
                    . ' FROM ' . $GLOBALS['prefix_fw'] . '_group_members '
                    . " WHERE idst = '" . $idst_group . "'";
                $re = sql_query($query_idstMember);
                while (list($idstMember) = sql_fetch_row($re)) {
                    if (!isset($this->user_org_chart[$idstMember])) {
                        $this->user_org_chart[$idstMember] = [];
                    }
                    $this->user_org_chart[$idstMember][$id_dir] = $id_dir;
                }
            }
        } else {
            $query = ' SELECT id_dir, translation '
                . ' FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
                . " WHERE lang_code = '" . getLanguage() . "'";
            $result = sql_query($query);
            while (list($id_dir, $dir_name) = sql_fetch_row($result)) {
                if ($id_dir == $this->org_chart_destination) {
                    $valid = preg_match('/' . $this->preg_match_folder . '/i', $dir_name, $match);
                    if ($valid) {
                        $dir_name = $match[1];
                    }

                    $this->org_chart_name[$dir_name] = $id_dir;
                    $idst_group = $this->org_chart_group['/oc_' . $id_dir];

                    $query_idstMember = 'SELECT idstMember'
                        . ' FROM ' . $GLOBALS['prefix_fw'] . '_group_members '
                        . " WHERE idst = '" . $idst_group . "'";
                    $re = sql_query($query_idstMember);
                    while (list($idstMember) = sql_fetch_row($re)) {
                        if (!isset($this->user_org_chart[$idstMember])) {
                            $this->user_org_chart[$idstMember] = [];
                        }

                        $this->user_org_chart[$idstMember][$id_dir] = $id_dir;
                    }
                }
            }

            $query = ' SELECT idOrg, code '
                . ' FROM ' . $GLOBALS['prefix_fw'] . '_org_chart_tree';
            $result = sql_query($query);
            while (list($id_dir, $dir_code) = sql_fetch_row($result)) {
                $valid = preg_match('/' . $this->preg_match_folder . '/i', $dir_code, $match);
                if ($valid) {
                    $dir_code = $match[1];
                }

                $this->org_chart_code[$dir_code] = $id_dir;
            }
        }

        return true;
    }

    public function close()
    {
        $this->directory = null;
        $this->tree_view = null;
        $this->people_view = null;
        $this->cols_descriptor = null;
        $this->arr_idst_inserted = [];
    }

    public function get_type_name()
    {
        return 'docebo-users';
    }

    public function get_type_description()
    {
        return 'connector to docebo users';
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function is_readonly()
    {
        return (bool) ($this->readwrite & 1);
    }

    public function is_writeonly()
    {
        return (bool) ($this->readwrite & 2);
    }

    public function get_tot_cols()
    {
        return count($this->cols_descriptor);
    }

    public function get_cols_descripor()
    {
        return $this->cols_descriptor;
    }

    public function get_first_row()
    {
        $this->simplecols = [];
        $export = [];
        foreach ($this->cols_descriptor as $field_id => $field_info) {
            $this->simplecols[$field_info[DOCEBOIMPORT_COLID]] = $field_info[DOCEBOIMPORT_COLNAME];
            $export[] = $field_info[DOCEBOIMPORT_COLNAME];
        }

        return $export;
    }

    public function get_next_row()
    {
        $pdr = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
        $pdr->idFilters = [key($this->user_org_chart)];
        next($this->user_org_chart);
        $this->data = $pdr->getRows();
        $row = sql_fetch_row($this->data); //print_r($row);
        if ($row == false) {
            $this->eof = true;

            return false;
        }

        $export = [];

        //find user field value
        foreach ($this->simplecols as $field_id => $name) {
            if (is_numeric($field_id)) {
                $p = $this->fl->fieldValue((int) $field_id, [$row[0]]);
                $export[] = reset($p);
            } else {
                switch ($field_id) {
                    case 'userid':
                            $export[] = substr($row[1], 1);

                        break;
                    case 'firstname':
                            $export[] = $row[2];

                        break;
                    case 'lastname':
                            $export[] = $row[3];

                        break;
                    case 'pass':
                            $export[] = '*****';

                        break;
                    case 'email':
                            $export[] = $row[4];

                        break;
                    case 'signature':
                            $export[] = $row[6];

                        break;
                }
            }
        }

        ++$this->index;

        return $export;
    }

    public function is_eof()
    {
        return $this->eof;
    }

    public function get_row_index()
    {
        return $this->index;
    }

    /**
     * @return int the number of mandatory columns to import
     **/
    public function get_tot_mandatory_cols()
    {
        $result = [];
        foreach ($this->cols_descriptor as $col) {
            if ($col[DOCEBOIMPORT_COLMANDATORY]) {
                $result[] = $col;
            }
        }

        return count($result);
    }

    public function get_row_bypk($pk)
    {
        // create field filter
        $arr_filter = [];
        foreach ($pk as $fieldname => $fieldvalue) {
            if (in_array($fieldname, $this->all_cols)) {
                $arr_filter[] = ['fieldname' => $fieldname, 'field_type' => 'text', 'value' => $fieldvalue];
            } else {
                $arr_filter[] = [FIELD_INFO_ID => $fieldname, FIELD_INFO_TYPE => 'text', 'value' => $fieldvalue];
            }
        }
        require_once _adm_ . '/lib/lib.directory.php';
        $this->people_view = new PeopleListView();
        $this->people_view->data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
        $this->people_view->data->idFilters = null;
        $this->people_view->data->resetFieldFilter();
        $this->people_view->data->resetCustomFilter();
        $this->people_view->addFieldFilters($arr_filter);
        $this->people_view->data->getRows(0, 1);
        $arr_result = $this->people_view->data->fetchRecord();

        if ($arr_result === false) {
            return false;
        }

        return $arr_result;
    }

    /**
     * Adds a "row", that is an array with the field retrieved by the connection
     * in the db, registering a new user.
     *
     * @param mixed $row
     * @param mixed $pk
     */
    public function add_row($row, $pk)
    {
        foreach ($pk as $key => $val) {        // Creating array of pk
            if ($val !== false) {
                $pk[$key] = addslashes(trim($val));
            }
        }
        foreach ($row as $key => $val) {       // Creating array of fields in row
            if ($val !== false) {
                $row[$key] = addslashes(trim($val));
            }
        }

        // All fields retrieved thanks to the csv.
        $updated = false;

        $userid = $row['userid'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $pass = $row['pass'];
        $email = $row['email'];
        $tree_code = $row['tree_code'];
        $language = $row['language'];

        $force_change = '';
        switch ($this->pwd_force_change_policy) {
            case 'by_setting':
                $force_change = FormaLms\lib\Get::sett('pass_change_first_login', 'off') == 'on' ? 1 : 0;
                break;
            case 'true':
                $force_change = 1;
                break;
            case 'false':
                $force_change = 0;
                break;
        }

        $arr_user = $this->get_row_bypk($pk);

        if ($arr_user === false) {  // User doesn't exist
            if ($firstname === null || $firstname === '') {
                $firstname = $this->default_cols['firstname'];
            }
            if ($lastname === null || $lastname === '') {
                $lastname = $this->default_cols['lastname'];
            }
            if ($pass === null || $pass === '') {
                $pass = $this->default_cols['pass'];
            }
            if ($email === null || $email === '') {
                $email = $this->default_cols['email'];
            }

            $idst = Docebo::aclm()->registerUser(
                $userid,
                $firstname,
                $lastname,
                $pass,
                $email,
                '', //avatar
                '', //signature
                false, //already_encripted
                false, //idst
                '', //pwd_expire_at
                $force_change,
                false, //facebook_id
                false, //twitter_id
                false, //linkedin_id
                false //google_id
            );

            if ($language !== LANG_NOT_SET_IN_CSV) {
                // Add language to the user

                $language = $this->return_valid_language_from_csv_row($language);

                if ($language !== null && $language != Lang::get()) {  // It's a valid and recognized and in platform language.
                    $this->add_language_to_user_by_idst($idst, $language);
                }
            }
        } else {    // Updating user that already exist
            $idst = $arr_user['idst'];
            if ($firstname === null || $firstname === '') {
                $firstname = false;
            }
            if ($lastname === null || $lastname === '') {
                $lastname = false;
            }
            if ($pass === null || $pass === '') {
                $pass = false;
            }
            if ($email === null || $email === '') {
                $email = false;
            }
            $result = Docebo::aclm()->updateUser(
                $idst,
                $userid,
                $firstname,
                $lastname,
                false,
                $email,
                false,
                false,
                false,
                true,
                '',
                false, //facebook_id
                false, //twitter_id
                false, //linkedin_id
                false //google_id
            );
            if (!$result) {
                $this->last_error = 'error on update user<br />';

                return false;
            }

            if ($language !== LANG_NOT_SET_IN_CSV) { // language set in csv...
                // Update language to the user only if it's different compared to
                // the actual.

                // check if language is valid, otherwise NULL
                $language = $this->return_valid_language_from_csv_row($language);

                // If lang_in_db is null, the user has default language.
                $lang_in_db = $this->get_lang_user_from_db($idst);

                if ($language != NOT_VALID_LANG) {
                    if ($lang_in_db != USER_HAS_NO_LANG) {
                        if ($language != $lang_in_db) {
                            // It's been added a language since the user has never been one (it has the def.).
                            $this->update_language_if_different($idst, $language);
                        }
                    } else {
                        $this->add_language_to_user_by_idst($idst, $language);
                    } // a language has never been set  to the user.
                }
            }

            $updated = true;
        }
        if ($idst !== false) {
            //destination folder
            if ($this->org_chart_destination > 0) {
                $res = DbConn::getInstance()->query('SELECT idst_oc, idst_ocd FROM %adm_org_chart_tree WHERE idOrg = ' . (int) $this->org_chart_destination);
                if ($res && DbConn::getInstance()->num_rows($res) > 0) {
                    list($oc, $ocd) = DbConn::getInstance()->fetch_row($res);
                    if ($oc && $ocd) {
                        Docebo::aclm()->addToGroup($oc, $idst);
                        Docebo::aclm()->addToGroup($ocd, $idst);
                    }
                }
            }

            if ($this->cache_inserted) {
                $this->arr_idst_inserted[] = $idst;
            }
            if ($this->sendnotify == 1) {
                // - Send alert ----------------------------------------------------
            }

            //Assign the user to correct folder

            if ($tree_code) {
                $tree_codes = explode('#', $tree_code);

                $readed_folders = [];
                foreach ($tree_codes as $tree_code) {
                    $dir_code = stripslashes($tree_code);
                    if (isset($this->org_chart_code[$dir_code])) {
                        $readed_folders[] = $this->org_chart_code[$dir_code];
                    }
                }

                if (!isset($this->user_org_chart[$idst])) {
                    $this->user_org_chart[$idst] = [];
                }

                $to_add = $readed_folders;
                if (!array_key_exists($idst, $this->inserted_user_org_chart)) {
                    $this->inserted_user_org_chart[$idst] = [];
                }

                foreach ($to_add as $orgChart) {
                    if (!in_array($orgChart, $this->inserted_user_org_chart[$idst])) {
                        $this->inserted_user_org_chart[$idst][] = $orgChart;
                    }
                }

                //Docebo::aclm()->removeFromAllGroup($idst);
                $query = 'select %adm_group.idst as idst from %adm_group join %adm_group_members on %adm_group.idst = %adm_group_members.idst where %adm_group_members.idstMember = ' . $idst . ' AND %adm_group.groupid like "/oc%"';
                $result = sql_query($query);
                $idstMembers = [];
                foreach ($result as $item) {
                    $idstMembers[] = $item['idst'];
                }

                Docebo::aclm()->removeFromGroup($idst, $idstMembers);

                require_once _lms_ . '/lib/lib.course.php';

                $query = 'select idCourse from `%lms_courseuser` where `idUser` = ' . $idst;

                $result = sql_query($query);

                foreach ($result as $item) {
                    $docebo_course = new DoceboCourse($item['idCourse']);
                    $level_idst = &$docebo_course->getCourseLevel($item['idCourse']);

                    Docebo::aclm()->addToGroup($level_idst[3], $idst);

                    $model = new SubscriptionAlms($item['idCourse'], false, false);
                    $model->subscribeUser($idst, 3, 0);
                }

                foreach ($this->inserted_user_org_chart[$idst] as $id_dir) {
                    $idst_oc_folder = $this->org_chart_group['/oc_' . $id_dir];
                    $idst_ocd_folder = $this->org_chart_group['/ocd_' . $id_dir];

                    Docebo::aclm()->addToGroup($idst_oc_folder, $idst);
                    Docebo::aclm()->addToGroup($idst_ocd_folder, $idst);

                    // adding to enrollment rules for org, if any
                    $enrollrules = new EnrollrulesAlms();
                    $users = [$idst];
                    $enrollrules->newRules('_NEW_IMPORTED_USER', $users, 'all', $id_dir);
                }
            }

            if ($updated) {
                $this->all_user_updated[] = $idst;
            }

            //  -------------------------------------------------------------------
            $result = true;
            Docebo::aclm()->addToGroup($this->tree_oc, $idst);
            Docebo::aclm()->addToGroup($this->tree_ocd, $idst);

            if ($this->tree != $this->tree_oc) {
                Docebo::aclm()->addToGroup($this->tree, $idst);
                Docebo::aclm()->addToGroup($this->tree_desc, $idst);
            }

            // add to group level
            $userlevel = Docebo::aclm()->getGroupST(ADMIN_GROUP_USER);
            Docebo::aclm()->addToGroup($userlevel, $idst);

            //-save extra field------------------------------------------

            $arr_fields_toset = [];
            foreach ($this->arr_fields as $field_id => $field_info) {
                if (isset($row[$field_id]) && $row[$field_id] !== false) {
                    $arr_fields_toset[$field_id] = $row[$field_id];
                }
            }

            if (count($arr_fields_toset) > 0) {
                $result = $this->fl->storeDirectFieldsForUser($idst, $arr_fields_toset);
            }
            //-----------------------------------------------------------
            if (!$result) {
                $this->last_error = 'error in store custom fields<br />';
            }

            return $result;
        } else {
            $this->last_error = 'error on register user<br />';

            return false;
        }
    }

    /**
     *  This func. resolve the name of the lang_browsercode, returning
     *        the language name only if it's recognised, otherwise it will
     *        return a null value.
     *
     * @param mixed $language
     */
    public function return_valid_language_from_csv_row($language)
    {
        $q_lang = 'SELECT lang_code FROM ' . $GLOBALS['prefix_fw'] . "_lang_language WHERE lang_browsercode LIKE '%$language%' ";
        $rs = sql_query($q_lang, $this->dbconn);

        return (($language = sql_fetch_row($rs)) != null) ? $language[0] : NOT_VALID_LANG;
    }

    /**
     * This func. return the last language set manually for the user or a
     * language setted by csv.
     */
    public function get_lang_user_from_db($idst)
    {
        if ($idst != null || $idst != '') {
            $path_name = 'ui.language';
            $q = 'SELECT value FROM ' . $GLOBALS['prefix_fw'] . '_setting_user WHERE id_user = ' . $idst . "  AND path_name = '" . $path_name . "'";

            $rs = sql_query($q, $this->dbconn);

            return (($language = sql_fetch_row($rs)) != null) ? $language[count($language) - 1] : null;
        }
    }

    /**
     * Adding a row in core_setting_user, with the path_name, id user and
     * language that he use.
     *
     * @param int    $idst
     * @param string $language
     */
    public function add_language_to_user_by_idst($idst, $language)
    {
        // Check the language var

        // Executing query for adding language in core_setting_user

        $path_name = 'ui.language'; // ???

        // Check if is only an import with connector.
        // Check if it's not a value accepted
        // TO add remove query from this table - why isn't working?
        $q_lang = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_setting_user (path_name, id_user, value) '
            . "VALUES ('" . $path_name . "', "
            . (int) $idst . ", '"
            . $language
            . "' )";

        $rs = sql_query($q_lang, $this->dbconn);
        if ($rs === false) {
            $this->last_error = Lang::t('_OPERATION_FAILURE', 'standard') . $query . ' [' . sql_error() . ']';

            return false;
        }
    }

    public function update_language_if_different($idst, $language)
    {
        // Executing query for updating language in core_setting_user

        $path_name = 'ui.language'; // ???

        $q_lang = 'UPDATE ' . $GLOBALS['prefix_fw'] . "_setting_user SET value = '" . $language . "' WHERE path_name = '" . $path_name
            . "' AND id_user = " . (int) $idst;

        $rs = sql_query($q_lang, $this->dbconn);

        if ($rs === false) {
            $this->last_error = Lang::t('_OPERATION_FAILURE', 'standard') . $query . ' [' . sql_error() . ']';

            return false;
        }
    }

    public function delete_bypk($pk)
    {
        $arr_people = $this->get_row_bypk($pk);
        if ($arr_people === false) {
            return false;
        } else {
            if ($this->canceled == '1') {
                Docebo::aclm()->suspendUser($arr_people['idst']);
            } else {
                Docebo::aclm()->deleteUser($arr_people['idst']);
            }
        }
    }

    public function delete_all_filtered($arr_pk)
    {
        // retrieve all users idst
        $arr_idst = [];
        foreach ($arr_pk as $pk) {
            $arr_user = $this->get_row_bypk($pk);
            $arr_idst[] = $arr_user['idst'];
        }
        $this->people_view->data->resetFieldFilter();
        $this->people_view->data->resetCustomFilter();
        $this->people_view->data->addFilter($arr_idst);
        $arr_idst_todelete = $this->people_view->data->getAllRowsIdst();
        foreach ($arr_idst_todelete as $id_st) {
            if ($this->canceled == '1') {
                Docebo::aclm()->suspendUser($id_st);
            } else {
                Docebo::aclm()->deleteUser($id_st);
            }
        }
    }

    public function delete_all_notinserted()
    {
        $this->people_view->data->resetFieldFilter();
        $this->people_view->data->resetCustomFilter();
        $this->people_view->addFieldFilters([['fieldname' => 'valid', 'value' => '1', 'field_type' => 'text']]);
        $this->people_view->data->addNotFilter($this->arr_idst_inserted);

        $idst_org = Docebo::user()->getAclManager()->getGroupST('oc_' . $this->org_chart_destination);
        $idst_orgd = Docebo::user()->getAclManager()->getGroupST('ocd_' . $this->org_chart_destination);

        $id_groups = UsermanagementAdm::getOrgGroups($this->org_chart_destination, true);

        $id_groups[] = $idst_org;
        $id_groups[] = $idst_orgd;
        $arr_members = Docebo::aclm()->getGroupUMembers($id_groups);
        $this->people_view->data->setUserFilter($arr_members);

        $idst_rs = $this->people_view->data->getAllRowsIdst();
        $counter = 0;
        if ($idst_rs !== false) {
            while (list($id_st) = sql_fetch_row($idst_rs)) {
                if ($this->canceled == '1') {
                    Docebo::aclm()->suspendUser($id_st);
                } else {
                    Docebo::aclm()->deleteUser($id_st);
                }
                ++$counter;
            }
        }

        return $counter;
    }

    public function get_error()
    {
        return $this->last_error;
    }
}

/**
 * The configurator for docebousers connectors.
 *
 * @version    1.1
 *
 * @author        Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboUsersUI extends DoceboConnectorUI
{
    public $connector = null;
    public $post_params = null;
    public $sh_next = true;
    public $sh_prev = false;
    public $sh_finish = false;
    public $step_next = '';
    public $step_prev = '';

    public $directory = null;

    public function DoceboConnectorDoceboUsersUI(&$connector)
    {
        require_once _base_ . '/lib/lib.userselector.php';
        $this->connector = $connector;
        $this->directory = new UserSelector();
    }

    public function _get_base_name()
    {
        return 'docebousersuiconfig';
    }

    public function get_old_name()
    {
        return $this->post_params['old_name'];
    }

    /**
     * All post fields are in array 'csvuiconfig'.
     **/
    public function parse_input($get, $post)
    {
        if (!isset($post[$this->_get_base_name()])) {
            // first call - first step, initialize variables
            $this->post_params = $this->connector->get_config();
            $this->post_params['step'] = '0';
            $this->post_params['old_name'] = $this->post_params['name'];
            if ($this->post_params['name'] == '') {
                $this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
            }
            // $this->post_params['org_chart_destination'] = $this->org_chart_destination;
        } else {
            // get previous values
            $this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
            $arr_new_params = $post[$this->_get_base_name()];
            // overwrite with the new posted values
            foreach ($arr_new_params as $key => $val) {
                if ($key != 'memory' && $key != 'reset') {
                    $this->post_params[$key] = stripslashes($val);
                }
            }
            if (isset($arr_new_params['reset'])) {
                $this->post_params['tree'] = '';
            } elseif ($this->directory->isParseDataAvailable($post)) {
                $arr_selection = $this->directory->getSelection($post);
                list($this->post_params['tree']) = $this->directory->getSelection($post);
            }
            $this->directory->resetSelection([$this->post_params['tree']]);

            $this->post_params['org_chart_destination'] =
                isset($arr_new_params['org_chart_destination'])
                    ? (int) $arr_new_params['org_chart_destination']
                    : $this->post_params['org_chart_destination'];
        }
        $this->_load_step_info();
    }

    public function _set_step_info($next, $prev, $sh_next, $sh_prev, $sh_finish)
    {
        $this->step_next = $next;
        $this->step_prev = $prev;
        $this->sh_next = $sh_next;
        $this->sh_prev = $sh_prev;
        $this->sh_finish = $sh_finish;
    }

    public function _load_step_info()
    {
        switch ($this->post_params['step']) {
            case '0':
                $this->_set_step_info('1', '0', true, false, false);
                break;
            case '1':
                $this->_set_step_info('1', '0', false, true, true);
                break;
            case '2':
                $this->_set_step_info('2', '1', false, true, true);
                break;
        }
    }

    public function go_next()
    {
        $this->post_params['step'] = $this->step_next;
        $this->_load_step_info();
    }

    public function go_prev()
    {
        $this->post_params['step'] = $this->step_prev;
        $this->_load_step_info();
    }

    public function go_finish()
    {
        $this->filterParams($this->post_params);
        $this->connector->set_config($this->post_params);
    }

    public function show_next()
    {
        return $this->sh_next;
    }

    public function show_prev()
    {
        return $this->sh_prev;
    }

    public function show_finish()
    {
        return $this->sh_finish;
    }

    public function get_htmlheader()
    {
        return '';
    }

    public function get_html()
    {
        $out = '';
        switch ($this->post_params['step']) {
            case '0':
                $out .= $this->_step0();
                break;
            case '1':
                $out .= $this->_step1();
                break;
            case '2':
                $out .= $this->_step2();
                break;
        }
        // save parameters
        $out .= $this->form->getHidden($this->_get_base_name() . '_memory',
            $this->_get_base_name() . '[memory]',
            urlencode(Util::serialize($this->post_params)));

        return $out;
    }

    public function _step0()
    {
        // ---- name -----
        $out = $this->form->getTextfield($this->lang->def('_NAME'),
            $this->_get_base_name() . '_name',
            $this->_get_base_name() . '[name]',
            255,
            $this->post_params['name']);
        // ---- description -----
        // ---- description -----
        $out .= $this->form->getSimpleTextarea($this->lang->def('_DESCRIPTION'),
            $this->_get_base_name() . '_description',
            $this->_get_base_name() . '[description]',
            $this->post_params['description']);
        // ---- access type read/write -----
        $out .= $this->form->getRadioSet($this->lang->def('_ACCESSTYPE'),
            $this->_get_base_name() . '_readwrite',
            $this->_get_base_name() . '[readwrite]',
            [$this->lang->def('_READ') => '1',
                $this->lang->def('_WRITE') => '2',
                $this->lang->def('_READWRITE') => '3', ],
            $this->post_params['readwrite']);
        // ---- access type read/write -----
        $out .= $this->form->getRadioSet($this->lang->def('_SENDNOTIFY'),
            $this->_get_base_name() . '_sendnotify',
            $this->_get_base_name() . '[sendnotify]',
            [$this->lang->def('_SEND') => '1',
                $this->lang->def('_DONTSEND') => '2', ],
            $this->post_params['sendnotify']);
        // ---- suspend users ----
        $out .= $this->form->getRadioSet($this->lang->def('_CANCELED_USERS'),
            $this->_get_base_name() . '_canceled',
            $this->_get_base_name() . '[canceled]',
            [$this->lang->def('_SUSPENDED') => '1',
                $this->lang->def('_DEL') => '2', ],
            $this->post_params['canceled']);

        $out .= $this->form->getTextfield($this->lang->def('_PREG_MATCH_FOLDER'),
            $this->_get_base_name() . '_preg_match_folder',
            $this->_get_base_name() . '[preg_match_folder]',
            255,
            $this->post_params['preg_match_folder']);

        $out .= $this->form->getRadioSet(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'),
            $this->_get_base_name() . '_pwd_force_change_policy',
            $this->_get_base_name() . '[pwd_force_change_policy]',
            [
                Lang::t('_NO', 'standard') => 'false',
                Lang::t('_YES', 'standard') => 'true',
                Lang::t('_SERVERINFO', 'configuration') => 'by_setting',
                Lang::t('_DO_NOTHING', 'preassessment') => 'do_nothing',
            ],
            $this->post_params['pwd_force_change_policy']);

        return $out;
    }

    public function _step1()
    {
        $GLOBALS['page']->add($this->form->getLineBox($this->lang->def('_NAME'),
            $this->post_params['name']));

        // ---- the tree selector -----
        /* $GLOBALS['page']->add($this->lang->def('_TREE_INSERT'));
        $this->directory->show_orgchart_selector = FALSE;
        $this->directory->show_orgchart_simple_selector = TRUE;
        $this->directory->multi_choice = FALSE;
        $this->directory->selector_mode = TRUE;
        $this->directory->loadOrgChartView(); */
        $umodel = new UsermanagementAdm();
        $out = $this->form->getDropdown(
            Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'),
            $this->_get_base_name() . '_org_chart_destination',
            $this->_get_base_name() . '[org_chart_destination]',
            $umodel->getOrgChartDropdownList(),
            $this->post_params['org_chart_destination']
        );
        // ---- add a button to reset selection -----
        /* $out = $this->form->getButton(	$this->_get_base_name().'_reset',
                                        $this->_get_base_name().'[reset]',
                                        $this->lang->def('_RESET')); */

        return $out;
    }

    public function _step2()
    {
        $out = $this->form->getLineBox($this->lang->def('_NAME'),
            $this->post_params['name']);

        $out .= $this->form->getTextfield($this->lang->def('_GROUP_FILTER'),
            $this->_get_base_name() . '_group',
            $this->_get_base_name() . '[group]',
            255,
            $this->post_params['group']);

        return $out;
    }
}

function docebousers_factory()
{
    return new DoceboConnectorDoceboUsers([]);
}

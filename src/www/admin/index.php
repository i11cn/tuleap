<?php
/**
  * Copyright 1999-2000 (c) The SourceForge Crew
  * Copyright (c) Enalean, 2011-2016. All Rights Reserved.
  *
  * This file is a part of Tuleap.
  *
  * Tuleap is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * Tuleap is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with Tuleap. If not, see <http://www.gnu.org/licenses/
  */

require_once('pre.php');
require_once('www/admin/admin_utils.php');
require_once('www/stats/site_stats_utils.php');
require_once('common/widget/Widget_Static.class.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

$em = EventManager::instance();

// Get various number of users and projects from status
$res = db_query("SELECT count(*) AS count FROM groups");
$row = db_fetch_array($res);
$total_groups = $row['count'];

db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
$row = db_fetch_array();
$pending_projects = $row['count'];

$res = db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
$row = db_fetch_array($res);
$active_groups = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='P'");
$row = db_fetch_array();
$realpending_users = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='V' OR status='W'");
$row = db_fetch_array();
$validated_users = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='R'");
$row = db_fetch_array();
$restricted_users = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='A'");
$row = db_fetch_array();
$actif_users = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='S'");
$row = db_fetch_array();
$hold_users = $row['count'];

db_query("SELECT count(*) AS count FROM user WHERE status='D'");
$row = db_fetch_array();
$deleted_users = $row['count'];

db_query("SELECT COUNT(DISTINCT(p.user_id)) AS count
          FROM user_preferences p
          JOIN user u USING (user_id)
          WHERE preference_name = 'use_lab_features'
            AND preference_value = 1
            AND (status = 'A'
              OR status = 'R')");
$row = db_fetch_array();
$mode_lab = $row['count'];

if($GLOBALS['sys_user_approval'] == 1){
    $pending_users = $realpending_users;

}else{
    $pending_users = $realpending_users + $validated_users ;
}



db_query("SELECT count(*) AS count FROM user WHERE status='V' OR status='W'");
$row = db_fetch_array();
$validated_users = $row['count'];

// Site Statistics
$wStats = new Widget_Static($Language->getText('admin_main', 'header_sstat'));
$wStats->setIcon('fa-pie-chart');
$wStats->setAdditionalClass('siteadmin-homepage-statistics');
$wStats->setContent('
    <section class="tlp-pane-section">
        <h2 class="tlp-pane-subtitle">'.$Language->getText('admin_main', 'stat_users').'</h2>

        <div class="tlp-property">
            <label class="tlp-label">'.$Language->getText('admin_main', 'status_user').'</label>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$actif_users.' '.$Language->getText('admin_main', 'statusactif_user').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$restricted_users.' '.$Language->getText('admin_main', 'statusrestricted_user').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$hold_users.' '.$Language->getText('admin_main', 'statushold_user').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$deleted_users.' '.$Language->getText('admin_main', 'statusdeleted_user').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$validated_users.' '.$Language->getText('admin_main', 'statusvalidated_user').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$realpending_users.' '.$Language->getText('admin_main', 'statuspending_user').'</span>
        </div>

        <div class="tlp-property">
            <label class="tlp-label">'.$Language->getText('admin_main', 'active_users').'</label>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.number_format(stats_getactiveusers(84600)).' '.$Language->getText('admin_main', 'lastday_users').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.number_format(stats_getactiveusers(592200)).' '.$Language->getText('admin_main', 'lastweek_users').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.number_format(stats_getactiveusers(2678400)).' '.$Language->getText('admin_main', 'lastmonth_users').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.number_format(stats_getactiveusers(8031600)).' '.$Language->getText('admin_main', 'last3months_users').'</span>
        </div>

        <div class="tlp-property">
            <label class="tlp-label">'.$Language->getText('admin_main', 'mode_lab_users').'</label>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$mode_lab.' '.$Language->getText('admin_main', 'mode_lab_users_nb_users').'</span>
        </div>

        <a href="lastlogins.php" class="tlp-button-primary tlp-button-outline tlp-button-wide">'.$Language->getText('admin_main', 'stat_login').'</a>
    </section>
    <section class="tlp-pane-section">
        <h2 class="tlp-pane-subtitle">'.$Language->getText('admin_main', 'stat_projects').'</h2>
        <div class="tlp-property">
            <label class="tlp-label">'.$Language->getText('admin_main', 'status_project').'</label>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$total_groups.' '.$Language->getText('admin_main', 'sstat_reg_g').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$active_groups.' '.$Language->getText('admin_main', 'sstat_reg_act_g').'</span>
            <span class="tlp-badge-secondary tlp-badge-outline siteadmin-homepage-stat-badge">'.$pending_projects.' '.$Language->getText('admin_main', 'sstat_pend_g').'</span>
        </div>
    </section>
    <section class="tlp-pane-section">
        <a href="/stats/" class="tlp-button-primary tlp-button-outline tlp-button-wide">'.$Language->getText('admin_main', 'stat_spu').'</a>
    </section>
');

if ($GLOBALS['sys_user_approval'] == 1) {
    $pending_action = '<p class="siteadmin-homepage-no-validation">'.$Language->getText('admin_main', 'review_pending_users_empty').'</p>';
    $pending_class  = '';

    if ($pending_users != 0) {
        $pending_action = '<a href="approve_pending_users.php?page=pending" class="tlp-button-primary tlp-button-wide">'.$Language->getText('admin_main', 'review_pending_users').'</a>';
        $pending_class  = 'tlp-text-warning';
    }

    $wUser = new Widget_Static($Language->getText('admin_main', 'header_user'));
    $wUser->setAdditionalClass('siteadmin-homepage-users');
    $wUser->setIcon('fa-group');
    $wUser->setContent('
        <section class="tlp-pane-section">
            <div class="siteadmin-homepage-validation">
                <svg width="131px" height="119px" viewBox="0 0 131 119" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g class="siteadmin-homepage-users-icon" transform="translate(-804.000000, -235.000000)">
                            <g transform="translate(804.000000, 235.000000)">
                                <path d="M121.33309,73.3625081 C120.205459,71.6407469 118.784544,70.0738272 117.260582,69.088873 C117.260582,69.088873 114.83151,71.6722795 111.634712,71.6722796 C108.437915,71.6722797 106.073975,69.5806879 106.073975,69.5806879 C105.758857,69.7892091 105.433006,70.0490531 105.103357,70.3513996 C103.453147,68.400827 101.627816,66.7272964 99.7205054,65.4945794 L96.8912642,63.6660082 C96.8912642,63.6660082 90.9959284,69.9359103 83.2373328,69.9359107 C75.4787372,69.935911 69.7414784,64.8596381 69.7414784,64.8596381 L68.990445,65.3566151 C67.4599597,66.3693741 65.9626801,67.6680083 64.5506672,69.1657782 L58.6868064,65.3758975 C58.6868064,65.3758975 50.4278428,74.1596031 39.5585791,74.1596035 C28.6893153,74.1596039 20.6518067,67.0480916 20.6518067,67.0480916 L14.5810515,71.0652567 C7.67198759,75.6371509 1.42793864,86.0243075 0.633030465,94.2666443 C0.633030465,94.2666443 0,100.007284 0,102.263463 L0,112.795575 C0,116.122509 2.68537935,118.803634 5.99796266,118.802901 C5.99796266,118.802901 10.7208672,118.801598 15.3621346,118.801598 L65.6486726,118.801598 C70.28994,118.801598 75.012845,118.801596 75.012845,118.801596 C78.3230936,118.801596 81.0108079,116.11199 81.0108077,112.794193 C81.0108077,112.794193 81.0108073,107.371793 81.0108073,102.263463 C81.0108073,102.122253 81.0099603,101.967635 81.0083724,101.801839 L101.860718,101.801839 C105.173704,101.801839 106.82912,101.801838 106.82912,101.801838 C110.134746,101.801838 112.826373,99.1123702 112.826373,95.7947443 C112.826373,95.7947443 112.826372,93.6431232 112.826372,89.9967434 C112.826372,88.3862589 112.799324,88.6292275 112.799324,88.6292275 C112.761013,87.3912253 112.597237,86.1066543 112.323109,84.802093 L119.308161,84.802093 C121.055516,84.8020929 123.556503,82.8400117 123.806104,80.3432657 L128.583729,80.3432657 C129.085918,80.3432657 130.826373,79.2623369 130.826373,77.9289441 C130.826373,77.9289441 130.826373,78.6746843 130.826373,77.9289441 C130.826373,77.5995756 130.826373,77.9289441 130.826373,77.9289441 C130.780339,76.3192019 129.3097,73.6699766 127.567401,72.5439084 C127.567401,72.5439084 126.361715,73.8261987 124.774964,73.8261988 C123.188213,73.8261988 122.014857,72.7880238 122.014857,72.7880238 C121.794454,72.9338692 121.564091,73.1293426 121.33309,73.3625081 Z M127.90959,64.1605055 C127.558405,63.5009504 127.322038,63.3052856 127.179625,63.3925426 C127.003779,63.500283 126.874433,63.8254744 126.613722,64.6452859 C126.546901,64.8926706 126.44769,65.1742952 126.327245,65.4644334 C126.447748,65.7409424 126.573177,66.0579315 126.690027,66.4064767 C125.579501,67.5153111 124.987221,68.1066894 124.098801,68.9198346 C123.654591,68.6980677 123.34231,68.517311 122.914241,68.254534 C123.070698,67.7968393 123.294712,67.3480578 123.5489,66.9264974 C122.994407,65.9835329 122.223835,65.4484034 121.888513,65.7680303 L121.856969,65.7920409 C121.484993,66.0146181 121.292432,66.4203789 121.247453,66.8811063 C121.396536,67.4307385 121.781797,68.1173273 122.735163,68.6207457 C123.207441,68.8701279 123.521523,69.0762172 123.868624,69.3291306 C122.591239,71.8231628 124.635282,73.4695787 124.468966,73.4435691 C123.816862,73.3415889 122.209186,72.8987494 121.101413,71.031818 C119.889695,68.9884214 120.725783,66.8700588 121.058212,66.1866989 C121.085796,66.1244716 121.116759,66.0641976 121.151218,66.0062024 C121.167965,65.9757866 121.17743,65.9599322 121.17743,65.9599322 C121.17743,65.9599322 121.177128,65.961611 121.176603,65.9648934 C121.300472,65.7699337 121.465741,65.6026566 121.677145,65.47616 L121.645601,65.5001706 C122.190041,64.981213 123.109736,65.5571192 123.763632,66.5868423 C124.637826,65.2643599 125.724353,64.2850725 125.724353,64.2850725 C125.724353,64.2850725 125.894565,64.5534474 126.119233,65.013709 C126.180594,64.8468069 126.232482,64.6868027 126.27222,64.5398312 C126.571035,63.5995209 126.709136,63.2547285 126.996615,63.0785899 C127.241054,62.9288222 127.484129,62.9935082 127.742191,63.2692532 C128.41703,63.8942415 129.385061,65.329156 129.343985,67.7036285 C129.301579,70.1550353 127.477536,72.8921815 124.982147,73.446991 C124.65464,73.0548138 122.572204,70.707198 125.061256,68.5502234 C127.550309,66.3932488 127.620082,65.412581 127.828031,64.7962694 C127.892494,64.6049845 127.914423,64.3801024 127.90959,64.1605055 Z M53.5591255,95.2633578 C53.5591255,88.114904 47.7666261,82.315215 40.6270338,82.315215 C33.4874415,82.315215 27.6949421,88.114904 27.6949421,95.2633578 C27.6949421,102.411812 33.4874415,108.211501 40.6270338,108.211501 C47.7666261,108.211501 53.5591255,102.411812 53.5591255,95.2633578 Z M115.752462,77.8791788 C115.752462,75.776722 114.04881,74.0709551 111.948959,74.0709551 C109.849109,74.0709551 108.145457,75.776722 108.145457,77.8791788 C108.145457,79.9816356 109.849109,81.6874025 111.948959,81.6874025 C114.04881,81.6874025 115.752462,79.9816356 115.752462,77.8791788 Z M126.818835,76.9070329 C126.818835,75.863465 125.973217,75.0167967 124.930942,75.0167967 C123.888668,75.0167967 123.043049,75.863465 123.043049,76.9070329 C123.043049,77.9506008 123.888668,78.7972691 124.930942,78.7972691 C125.973217,78.7972691 126.818835,77.9506008 126.818835,76.9070329 Z M93.2310712,84.999994 C93.2310712,79.897352 89.0963238,75.7574725 84.0000072,75.7574725 C78.9036905,75.7574725 74.7689431,79.897352 74.7689431,84.999994 C74.7689431,90.1026361 78.9036905,94.2425155 84.0000072,94.2425155 C89.0963238,94.2425155 93.2310712,90.1026361 93.2310712,84.999994 Z M61.0308068,7.94947819 C58.6251842,3.43151711 57.006072,2.09121048 56.0305363,2.68892172 C54.8259941,3.42694532 53.9399694,5.6545102 52.1540979,11.2702301 C51.696368,12.9648185 51.016775,14.8939508 50.1917215,16.8814014 C51.0171708,18.7754913 51.8763631,20.946871 52.6767844,23.3344102 C45.0696716,30.92994 41.0125448,34.9808892 34.9268545,40.5509444 C31.8840094,39.0318385 29.7448829,37.7936524 26.8126009,35.9936265 C27.8843315,32.8584124 29.4188336,29.784253 31.1600275,26.8965586 C27.3617386,20.4372398 22.0833106,16.7715957 19.7863529,18.9610437 L19.5702779,19.1255166 C17.0222367,20.6501738 15.703187,23.4296404 15.3950815,26.5856289 C16.4163047,30.3506171 19.0553484,35.0537589 25.5859189,38.5021814 C28.8210291,40.2104534 30.9724915,41.6221673 33.3501357,43.3546276 C24.6000317,60.4387811 38.6017554,71.7167511 37.4624876,71.5385851 C32.9955682,70.8400197 21.9829661,67.8065631 14.3947045,55.0180587 C6.09442022,41.020765 11.821635,26.5099532 14.098782,21.828929 C14.2877327,21.4026713 14.499827,20.9897939 14.7358707,20.5925256 C14.8505855,20.3841771 14.9154262,20.2755743 14.9154262,20.2755743 C14.9154262,20.2755743 14.9133585,20.287074 14.9097566,20.3095588 C15.7582623,18.9740824 16.8903564,17.8282319 18.3384758,16.9617287 L18.1224009,17.1262017 C21.8518245,13.5713348 28.1517449,17.5162999 32.6309438,24.5699165 C38.619181,15.5108949 46.0619036,8.8027633 46.0619036,8.8027633 C46.0619036,8.8027633 47.2278594,10.6411351 48.7668378,13.7939333 C49.1871645,12.6506513 49.5425976,11.5546204 49.8148035,10.5478642 C51.8616899,4.10672615 52.8076859,1.74489327 54.776921,0.538341939 C56.4513292,-0.487568808 58.1163959,-0.0444687988 59.8841238,1.84438826 C64.123603,5.77069803 70.0523086,14.3633808 70.789004,28.3073989 C73.2156747,28.6075768 76.0683858,31.0361376 78.2923134,34.5382667 C82.5667806,28.0718411 87.8794748,23.2835057 87.8794748,23.2835057 C87.8794748,23.2835057 88.7117464,24.595755 89.8102855,26.8462558 C90.1103191,26.0301689 90.364031,25.2478105 90.5583345,24.5291772 C92.0194237,19.9314246 92.6946857,18.2455233 94.1003466,17.3842742 C95.2955569,16.6519682 96.4840991,16.9682577 97.745922,18.3165439 C101.04562,21.372494 105.778919,28.388669 105.578075,39.998918 C105.370723,51.9853458 96.4518611,65.368929 84.250379,68.0817318 C82.648997,66.1641374 72.4666893,54.6852076 84.6371913,44.1384388 C96.8076933,33.5916701 97.1488535,28.796585 98.1656437,25.7830603 C98.4808425,24.8477514 98.5880679,23.7481652 98.5644365,22.6744217 C96.8472776,19.4494533 95.6915382,18.4927282 94.9951904,18.9193808 C94.1353754,19.4461899 93.5029216,21.0362493 92.2281476,25.0448097 C91.9014152,26.2544248 91.4163145,27.6314599 90.8273826,29.0501232 C91.416597,30.4021446 92.0298976,31.9520984 92.6012468,33.6563492 C87.1712091,39.0781188 84.275189,41.9697293 79.9311588,45.9456937 C77.7591437,44.8613398 76.2322125,43.9775095 74.1391186,42.6926319 C74.9041313,40.4546829 75.9994751,38.2603154 77.2423577,36.1990484 C75.2632211,32.8333554 72.7211016,30.5302589 70.8547644,30.139761 C70.8678401,30.8210871 70.86864,31.5144483 70.8564362,32.2199168 C70.8019279,35.3708938 70.3176978,38.5907896 69.4539968,41.7679013 C70.4529686,42.7342698 71.7024882,43.6589836 73.2635,44.483265 C75.5727559,45.7026477 77.1084925,46.710344 78.8056802,47.9469925 C72.5597632,60.1418423 82.554342,68.1921767 81.7411205,68.065 C78.5525861,67.5663565 70.6916741,65.4010431 65.2750926,56.2724732 C64.8242986,55.5122708 64.4314698,54.7499464 64.0911112,53.988752 C58.7395871,62.5677048 50.5850387,69.4260079 40.9777877,71.5620252 C38.7343606,68.8756063 24.4696414,52.7944077 41.5196853,38.0191034 C58.5697293,23.2437991 59.0476716,16.5262117 60.4721253,12.304469 C60.9136974,10.994165 61.0639129,9.45371963 61.0308068,7.94947819 Z M117.949985,52.1989875 C117.242458,50.8701941 116.766256,50.4759918 116.479337,50.6517868 C116.125065,50.8688495 115.864474,51.5240064 115.339225,53.1756655 C115.2046,53.6740668 115.004723,54.2414507 114.762063,54.8259868 C115.004839,55.3830642 115.257539,56.0216963 115.492954,56.7239038 C113.255599,58.9578517 112.062343,60.1492906 110.27246,61.787519 C109.377518,61.3407295 108.748372,60.9765622 107.885948,60.4471503 C108.201159,59.5250415 108.652476,58.6208897 109.164585,57.7715797 C108.047457,55.8718069 106.495,54.7936914 105.819434,55.4376376 L105.755883,55.4860114 C105.00647,55.9344336 104.618519,56.7519123 104.527901,57.6801311 C104.828257,58.787465 105.604435,60.1707225 107.525164,61.1849502 C108.476654,61.687376 109.109428,62.1025802 109.808725,62.6121201 C107.235202,67.6368004 111.353298,70.9538037 111.018224,70.9014027 C109.704442,70.6959452 106.465487,69.8037646 104.233677,66.0424928 C101.792451,61.9256996 103.476903,57.6578739 104.146642,56.2811215 C104.202215,56.1557533 104.264595,56.0343205 104.334019,55.9174785 C104.367758,55.8562004 104.386828,55.8242588 104.386828,55.8242588 C104.386828,55.8242588 104.38622,55.8276411 104.385161,55.8342541 C104.634718,55.4414725 104.967682,55.1044624 105.393593,54.8496121 L105.330043,54.8979858 C106.426917,53.8524515 108.279808,55.012719 109.597201,57.0872829 C111.358423,54.4229023 113.547428,52.4499502 113.547428,52.4499502 C113.547428,52.4499502 113.890351,52.9906402 114.342986,53.9179207 C114.466609,53.581666 114.571147,53.2593085 114.651207,52.9632079 C115.253223,51.0687822 115.531454,50.3741353 116.110632,50.0192722 C116.603098,49.717538 117.092817,49.8478597 117.61273,50.4033981 C118.972314,51.6625503 120.922589,54.5534458 120.839835,59.3372513 C120.754399,64.2760549 117.079534,69.7905325 112.052121,70.9082968 C111.392299,70.1181847 107.196852,65.388487 112.2115,61.0428704 C117.226148,56.6972539 117.366718,54.7215207 117.785669,53.4798491 C117.915541,53.094471 117.959722,52.6414052 117.949985,52.1989875 Z M125.874889,76.4344738 C125.874889,76.8085831 125.619237,76.9513353 125.432414,77.0571688 C125.316879,77.1236225 125.245591,77.2442235 125.245591,77.3008321 C125.245591,77.3451345 125.211177,77.3795919 125.166929,77.3795919 L124.694956,77.3795919 C124.650708,77.3795919 124.616294,77.3451345 124.616294,77.3008321 L124.616294,77.2122273 C124.616294,76.9734865 124.854738,76.7692032 125.02927,76.6904433 C125.17922,76.6215285 125.245591,76.5575361 125.245591,76.4295514 C125.245591,76.3212566 125.100558,76.2203455 124.943233,76.2203455 C124.854738,76.2203455 124.773618,76.2498805 124.731829,76.2794154 C124.685123,76.3138728 124.635959,76.3581753 124.520424,76.50585 C124.505675,76.5255399 124.481093,76.5353849 124.458969,76.5353849 C124.441762,76.5353849 124.424555,76.5304624 124.412264,76.5206174 L124.087782,76.2744929 C124.055825,76.2498805 124.045993,76.2055781 124.068116,76.1711206 C124.279521,75.8191626 124.579421,75.6468754 124.980106,75.6468754 C125.402916,75.6468754 125.874889,75.984066 125.874889,76.4344738 Z M125.245591,78.0884305 C125.245591,78.1327329 125.211177,78.1671904 125.166929,78.1671904 L124.694956,78.1671904 C124.650708,78.1671904 124.616294,78.1327329 124.616294,78.0884305 L124.616294,77.6158715 C124.616294,77.5715691 124.650708,77.5371116 124.694956,77.5371116 L125.166929,77.5371116 C125.211177,77.5371116 125.245591,77.5715691 125.245591,77.6158715 L125.245591,78.0884305 Z M113.850711,76.9271229 C113.850711,77.6808338 113.335653,77.968434 112.959265,78.1816549 C112.726498,78.3155377 112.582877,78.5585103 112.582877,78.6725587 C112.582877,78.761814 112.513542,78.8312347 112.424397,78.8312347 L111.473522,78.8312347 C111.384377,78.8312347 111.315042,78.761814 111.315042,78.6725587 L111.315042,78.4940482 C111.315042,78.0130616 111.795433,77.6014958 112.147058,77.4428198 C112.44916,77.3039783 112.582877,77.1750541 112.582877,76.9172056 C112.582877,76.6990261 112.29068,76.4957225 111.973722,76.4957225 C111.795433,76.4957225 111.632001,76.555226 111.547809,76.6147295 C111.453712,76.6841503 111.354662,76.7734055 111.121896,77.070923 C111.092181,77.110592 111.042656,77.1304265 110.998084,77.1304265 C110.963416,77.1304265 110.928749,77.1205092 110.903987,77.1006747 L110.25026,76.6048123 C110.185877,76.555226 110.166067,76.4659708 110.21064,76.39655 C110.636553,75.6874667 111.240755,75.340363 112.048009,75.340363 C112.899835,75.340363 113.850711,76.0196946 113.850711,76.9271229 Z M112.582877,80.2593186 C112.582877,80.3485738 112.513542,80.4179946 112.424397,80.4179946 L111.473522,80.4179946 C111.384377,80.4179946 111.315042,80.3485738 111.315042,80.2593186 L111.315042,79.3072627 C111.315042,79.2180074 111.384377,79.1485867 111.473522,79.1485867 L112.424397,79.1485867 C112.513542,79.1485867 112.582877,79.2180074 112.582877,79.3072627 L112.582877,80.2593186 Z M88.6155392,82.6893637 C88.6155392,84.5186127 87.3654993,85.2166156 86.4520085,85.7341006 C85.8870867,86.059033 85.5385178,86.6487251 85.5385178,86.9255194 C85.5385178,87.1421409 85.3702432,87.3106244 85.1538902,87.3106244 L82.8461241,87.3106244 C82.6297711,87.3106244 82.4614965,87.1421409 82.4614965,86.9255194 L82.4614965,86.4922762 C82.4614965,85.3249264 83.6273991,84.3260602 84.4807917,83.9409551 C85.2139882,83.6039882 85.5385178,83.2910903 85.5385178,82.6652946 C85.5385178,82.1357751 84.8293606,81.6423593 84.0601052,81.6423593 C83.6273991,81.6423593 83.2307518,81.7867737 83.0264184,81.9311881 C82.7980457,82.0996715 82.5576534,82.3162931 81.9927315,83.0383651 C81.9206138,83.1346414 81.8004177,83.1827795 81.6922411,83.1827795 C81.6081038,83.1827795 81.5239665,83.1587105 81.4638685,83.1105723 L79.8772793,81.907119 C79.7210243,81.7867737 79.6729459,81.5701521 79.7811224,81.4016686 C80.8148093,79.6807304 82.2812023,78.838313 84.2403994,78.838313 C86.3077732,78.838313 88.6155392,80.4870441 88.6155392,82.6893637 Z M85.5385178,90.77657 C85.5385178,90.9931916 85.3702432,91.161675 85.1538902,91.161675 L82.8461241,91.161675 C82.6297711,91.161675 82.4614965,90.9931916 82.4614965,90.77657 L82.4614965,88.4659396 C82.4614965,88.249318 82.6297711,88.0808345 82.8461241,88.0808345 L85.1538902,88.0808345 C85.3702432,88.0808345 85.5385178,88.249318 85.5385178,88.4659396 L85.5385178,90.77657 Z M47.0930797,92.0263221 C47.0930797,94.5889753 45.3418589,95.5668299 44.0621207,96.291791 C43.2707036,96.7469991 42.7823824,97.5731176 42.7823824,97.9608875 C42.7823824,98.2643596 42.5466412,98.5003935 42.2435453,98.5003935 L39.0105223,98.5003935 C38.7074264,98.5003935 38.4716852,98.2643596 38.4716852,97.9608875 L38.4716852,97.3539433 C38.4716852,95.7185659 40.1050353,94.3192224 41.3005803,93.7797164 C42.3277386,93.3076487 42.7823824,92.8693001 42.7823824,91.992603 C42.7823824,91.2507823 41.7889014,90.5595403 40.7112271,90.5595403 C40.1050353,90.5595403 39.5493595,90.761855 39.2631023,90.9641697 C38.9431677,91.2002036 38.6063945,91.5036757 37.8149774,92.5152494 C37.7139454,92.6501258 37.5455588,92.7175641 37.3940109,92.7175641 C37.2761403,92.7175641 37.1582696,92.683845 37.0740763,92.6164067 L34.8513731,90.9304506 C34.6324705,90.761855 34.5651158,90.4583829 34.7166638,90.2223491 C36.1647886,87.8114319 38.2191053,86.6312626 40.963807,86.6312626 C43.8600567,86.6312626 47.0930797,88.9410224 47.0930797,92.0263221 Z M42.7823824,103.355947 C42.7823824,103.659419 42.5466412,103.895453 42.2435453,103.895453 L39.0105223,103.895453 C38.7074264,103.895453 38.4716852,103.659419 38.4716852,103.355947 L38.4716852,100.118911 C38.4716852,99.8154392 38.7074264,99.5794054 39.0105223,99.5794054 L42.2435453,99.5794054 C42.5466412,99.5794054 42.7823824,99.8154392 42.7823824,100.118911 L42.7823824,103.355947 Z" id="Combined-Shape"></path>
                            </g>
                        </g>
                    </g>
                </svg>
                <span class="siteadmin-homepage-validation-count '.$pending_class.'">'.$pending_users.'</span>
            </div>
            '.$pending_action.'
        </section>
    ');
}

if ($GLOBALS['sys_project_approval'] == 1) {
    $groups_pending       = '<p class="siteadmin-homepage-no-validation">'.$Language->getText('admin_main', 'review_pending_projects_empty').'</p>';
    $groups_pending_class = '';

    if ($pending_projects != 0) {
        $groups_pending       = '<a href="approve-pending.php" class="tlp-button-primary tlp-button-wide">'.$Language->getText('admin_main', 'review_pending_projects').'</a>';
        $groups_pending_class = 'tlp-text-warning';
    }

    $wProject = new Widget_Static($Language->getText('admin_main', 'header_group'));
    $wProject->setAdditionalClass('siteadmin-homepage-projects');
    $wProject->setIcon('fa-archive');
    $wProject->setContent('
        <section class="tlp-pane-section">
            <div class="siteadmin-homepage-validation">
                <svg width="121px" height="75px" viewBox="0 0 121 75" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g fill-rule="evenodd">
                        <path class="siteadmin-homepage-projects-icon" d="M78.2857265,34.2637155 L102.728863,34.2637155 C103.567308,34.2637155 104.26202,34.9584274 104.26202,35.7968728 L104.26202,38.7585748 L114.290471,38.7585748 C114.603075,38.7585748 114.86209,39.0175896 114.86209,39.3301936 L114.86209,41.616669 C114.86209,41.9292731 114.603075,42.1882878 114.290471,42.1882878 L104.26202,42.1882878 L104.26202,42.5852749 L113.693905,42.5852749 C114.000142,42.5852749 114.254951,42.8338467 114.265203,43.1378622 L120.559702,43.1378622 C120.72507,43.1378622 120.86209,43.2748818 120.86209,43.4402503 L120.86209,44.6498026 C120.86209,44.815171 120.72507,44.9521906 120.559702,44.9521906 L114.265524,44.9521906 L114.265524,45.162198 L120.244116,45.162198 C120.409485,45.162198 120.546505,45.2992175 120.546505,45.464586 L120.546505,50.0004071 C120.546505,50.1657756 120.409485,50.3027951 120.244116,50.3027951 L114.265524,50.3027951 L114.265524,51.7311763 C114.265524,52.0437804 114.006509,52.3027951 113.693905,52.3027951 L104.26202,52.3027951 L104.26202,58.7942323 C104.26202,59.6326777 103.567308,60.3273896 102.728863,60.3273896 L78.2857265,60.3273896 L78.2857265,71.1904844 C78.2857265,72.9059794 76.8643163,74.3273896 75.1488213,74.3273896 L6.13690527,74.3273896 C4.4214102,74.3273896 3,72.9059794 3,71.1904844 L3,24.1369053 C3,22.4214102 4.4214102,21 6.13690527,21 L75.1488213,21 C76.818464,21 78.2095325,22.3464418 78.2827017,24 L104.328933,24 C105.167378,24 105.86209,24.6947119 105.86209,25.5331573 L105.86209,31.6657865 C105.86209,32.5042319 105.167378,33.1989438 104.328933,33.1989438 L78.2857265,33.1989438 L78.2857265,34.2637155 Z M116.924649,47.202443 C116.256835,47.202443 115.715024,47.7442544 115.715024,48.4120685 C115.715024,49.0798826 116.256835,49.621694 116.924649,49.621694 C117.592463,49.621694 118.134275,49.0798826 118.134275,48.4120685 C118.134275,47.7442544 117.592463,47.202443 116.924649,47.202443 Z M117.125888,49.2247867 C117.125888,49.2530861 117.103878,49.2750967 117.075578,49.2750967 L116.773719,49.2750967 C116.745419,49.2750967 116.723409,49.2530861 116.723409,49.2247867 L116.723409,48.9229271 C116.723409,48.8946278 116.745419,48.8726172 116.773719,48.8726172 L117.075578,48.8726172 C117.103878,48.8726172 117.125888,48.8946278 117.125888,48.9229271 L117.125888,49.2247867 Z M117.243729,48.564758 C117.169702,48.6072839 117.124026,48.6844605 117.124026,48.7206863 C117.124026,48.7490369 117.101976,48.7710874 117.073625,48.7710874 L116.771219,48.7710874 C116.742868,48.7710874 116.720818,48.7490369 116.720818,48.7206863 L116.720818,48.6639851 C116.720818,48.5112069 116.873596,48.3804791 116.985423,48.330078 C117.0815,48.2859771 117.124026,48.2450263 117.124026,48.1631245 C117.124026,48.0938231 117.031099,48.0292467 116.930297,48.0292467 C116.873596,48.0292467 116.82162,48.0481471 116.794844,48.0670475 C116.764919,48.089098 116.733418,48.1174486 116.659391,48.2119506 C116.649941,48.2245508 116.634191,48.230851 116.620015,48.230851 C116.60899,48.230851 116.597965,48.2277009 116.59009,48.2214008 L116.382185,48.0638974 C116.36171,48.0481471 116.35541,48.0197965 116.369585,47.997746 C116.505038,47.7725163 116.697192,47.662264 116.953923,47.662264 C117.224828,47.662264 117.527235,47.8780435 117.527235,48.1662746 C117.527235,48.4056796 117.363431,48.4970316 117.243729,48.564758 Z M117.802634,46.3321585 C117.802634,46.497527 117.665614,46.6345466 117.500246,46.6345466 L116.290693,46.6345466 C116.125325,46.6345466 115.988305,46.497527 115.988305,46.3321585 C115.988305,46.16679 116.125325,46.0297704 116.290693,46.0297704 L117.500246,46.0297704 C117.665614,46.0297704 117.802634,46.16679 117.802634,46.3321585 Z M107.418955,46.442049 C106.156553,46.442049 105.132341,47.4662614 105.132341,48.7286628 C105.132341,49.9910642 106.156553,51.0152766 107.418955,51.0152766 C108.681356,51.0152766 109.705568,49.9910642 109.705568,48.7286628 C109.705568,47.4662614 108.681356,46.442049 107.418955,46.442049 Z M107.799367,50.2649836 C107.799367,50.3184792 107.757759,50.3600869 107.704263,50.3600869 L107.133643,50.3600869 C107.080148,50.3600869 107.03854,50.3184792 107.03854,50.2649836 L107.03854,49.6943637 C107.03854,49.6408681 107.080148,49.5992604 107.133643,49.5992604 L107.704263,49.5992604 C107.757759,49.5992604 107.799367,49.6408681 107.799367,49.6943637 L107.799367,50.2649836 Z M108.022126,49.0172992 C107.88219,49.097688 107.795847,49.2435787 107.795847,49.312058 C107.795847,49.3656505 107.754164,49.4073336 107.700571,49.4073336 L107.128918,49.4073336 C107.075325,49.4073336 107.033642,49.3656505 107.033642,49.312058 L107.033642,49.204873 C107.033642,48.9160689 107.322446,48.6689479 107.533839,48.5736723 C107.715458,48.4903062 107.795847,48.4128948 107.795847,48.258072 C107.795847,48.127068 107.620182,48.0049962 107.429631,48.0049962 C107.322446,48.0049962 107.224193,48.0407246 107.173578,48.0764529 C107.117008,48.118136 107.057461,48.1717285 106.917525,48.3503702 C106.899661,48.3741891 106.869887,48.3860985 106.843091,48.3860985 C106.822249,48.3860985 106.801408,48.3801438 106.786521,48.3682343 L106.393509,48.0704982 C106.354804,48.0407246 106.342894,47.987132 106.36969,47.945449 C106.625743,47.5196862 106.988982,47.3112709 107.474292,47.3112709 C107.986398,47.3112709 108.558051,47.7191695 108.558051,48.2640267 C108.558051,48.7165857 108.248406,48.8892727 108.022126,49.0172992 Z M109.078651,44.796908 C109.078651,45.1095121 108.819636,45.3685269 108.507032,45.3685269 L106.220557,45.3685269 C105.907953,45.3685269 105.648938,45.1095121 105.648938,44.796908 C105.648938,44.484304 105.907953,44.2252892 106.220557,44.2252892 L108.507032,44.2252892 C108.819636,44.2252892 109.078651,44.484304 109.078651,44.796908 Z M85.8986172,44.6080936 C82.5126898,44.6080936 79.7656166,47.3551668 79.7656166,50.7410942 C79.7656166,54.1270216 82.5126898,56.8740947 85.8986172,56.8740947 C89.2845446,56.8740947 92.0316177,54.1270216 92.0316177,50.7410942 C92.0316177,47.3551668 89.2845446,44.6080936 85.8986172,44.6080936 Z M86.9189322,54.8617095 C86.9189322,55.0051918 86.8073348,55.1167892 86.6638525,55.1167892 L85.1333745,55.1167892 C84.9898922,55.1167892 84.8782949,55.0051918 84.8782949,54.8617095 L84.8782949,53.3312315 C84.8782949,53.1877492 84.9898922,53.0761519 85.1333745,53.0761519 L86.6638525,53.0761519 C86.8073348,53.0761519 86.9189322,53.1877492 86.9189322,53.3312315 L86.9189322,54.8617095 Z M87.5164028,51.5152551 C87.141076,51.7308684 86.9094913,52.1221667 86.9094913,52.3058372 C86.9094913,52.4495794 86.7976918,52.5613789 86.6539496,52.5613789 L85.1206995,52.5613789 C84.9769573,52.5613789 84.8651578,52.4495794 84.8651578,52.3058372 L84.8651578,52.0183528 C84.8651578,51.2437421 85.6397686,50.5809308 86.2067517,50.3253891 C86.693878,50.1017902 86.9094913,49.8941625 86.9094913,49.4789073 C86.9094913,49.1275375 86.4383363,48.8001247 85.927253,48.8001247 C85.6397686,48.8001247 85.3762412,48.8959528 85.2404847,48.991781 C85.0887568,49.1035804 84.9290432,49.2473226 84.5537164,49.7264633 C84.5058023,49.7903487 84.4259455,49.8222914 84.3540744,49.8222914 C84.2981747,49.8222914 84.2422749,49.8063201 84.2023465,49.7743774 L83.1482371,48.9758096 C83.0444233,48.8959528 83.0124806,48.7522106 83.0843517,48.6404111 C83.7711199,47.4984592 84.7453726,46.9394617 86.0470381,46.9394617 C87.4205747,46.9394617 88.9538249,48.0334996 88.9538249,49.4948787 C88.9538249,50.7087017 88.1233144,51.171871 87.5164028,51.5152551 Z M90.3501429,40.195608 C90.3501429,41.0340534 89.655431,41.7287653 88.8169856,41.7287653 L82.6843564,41.7287653 C81.845911,41.7287653 81.1511991,41.0340534 81.1511991,40.195608 C81.1511991,39.3571626 81.845911,38.6624507 82.6843564,38.6624507 L88.8169856,38.6624507 C89.655431,38.6624507 90.3501429,39.3571626 90.3501429,40.195608 Z M40.71342,42.1650391 C33.785668,42.1650391 28.1650391,47.785668 28.1650391,54.71342 C28.1650391,61.6411719 33.785668,67.2618009 40.71342,67.2618009 C47.6411719,67.2618009 53.2618009,61.6411719 53.2618009,54.71342 C53.2618009,47.785668 47.6411719,42.1650391 40.71342,42.1650391 Z M42.801028,63.1443749 C42.801028,63.4379458 42.5726951,63.6662787 42.2791241,63.6662787 L39.1477009,63.6662787 C38.85413,63.6662787 38.625797,63.4379458 38.625797,63.1443749 L38.625797,60.0129516 C38.625797,59.7193807 38.85413,59.4910477 39.1477009,59.4910477 L42.2791241,59.4910477 C42.5726951,59.4910477 42.801028,59.7193807 42.801028,60.0129516 L42.801028,63.1443749 Z M44.0234785,56.2973864 C43.2555438,56.7385404 42.7817117,57.5391532 42.7817117,57.9149511 C42.7817117,58.2090538 42.5529651,58.4378003 42.2588625,58.4378003 L39.1217672,58.4378003 C38.8276646,58.4378003 38.598918,58.2090538 38.598918,57.9149511 L38.598918,57.3267457 C38.598918,55.7418591 40.1838047,54.385719 41.3438764,53.8628698 C42.3405577,53.4053767 42.7817117,52.9805617 42.7817117,52.1309318 C42.7817117,51.4120141 41.8177084,50.7421136 40.77201,50.7421136 C40.1838047,50.7421136 39.6446164,50.938182 39.3668528,51.1342505 C39.0564111,51.362997 38.7296303,51.6570997 37.9616956,52.6374419 C37.8636613,52.7681542 37.700271,52.8335104 37.5532196,52.8335104 C37.4388464,52.8335104 37.3244731,52.8008323 37.2427779,52.7354762 L35.0860249,51.1015724 C34.8736174,50.938182 34.8082613,50.6440793 34.9553126,50.4153328 C36.3604699,48.0788504 38.3538325,46.9351178 41.0170956,46.9351178 C43.8274101,46.9351178 46.9645053,49.1735659 46.9645053,52.1636098 C46.9645053,54.6471436 45.2652454,55.5948077 44.0234785,56.2973864 Z M49.8214316,31.5714316 C49.8214316,32.9776833 48.4000214,34.1428633 46.6845264,34.1428633 L34.1369053,34.1428633 C32.4214102,34.1428633 31,32.9776833 31,31.5714316 C31,30.16518 32.4214102,29 34.1369053,29 L46.6845264,29 C48.4000214,29 49.8214316,30.16518 49.8214316,31.5714316 Z M81.5595371,3.13690527 C81.5595371,1.4214102 80.1381269,0 78.4226318,0 L3.13690527,0 C1.4214102,0 0,1.4214102 0,3.13690527 L0,15.6845264 C0,17.4000214 1.4214102,18.8214316 3.13690527,18.8214316 L78.4226318,18.8214316 C80.1381269,18.8214316 81.5595371,17.4000214 81.5595371,15.6845264 L81.5595371,3.13690527 Z" id="Combined-Shape" fill="#000000"></path>
                    </g>
                </svg>
                <span class="siteadmin-homepage-validation-count '.$groups_pending_class.'">'.$pending_projects.'</span>
            </div>
            '.$groups_pending.'
        </section>
    ');
}

// Plugins
$plugins = array();
$em->processEvent('site_admin_option_hook', array(
    'plugins' => &$plugins
));
$plugins_content = array_reduce(
    $plugins,
    function ($plugins_content, $plugin) {
        return $plugins_content . '<li><a href="'. $plugin['href'] .'">'. $plugin['label'] .'</a></li>';
    },
    ''
);

$wPlugins = new Widget_Static($Language->getText('admin_main', 'header_plugins'));
$wPlugins->setAdditionalClass('siteadmin-homepage-plugins');
$wPlugins->setIcon('fa-cubes');
$wPlugins->setContent('
    <section class="tlp-pane-section siteadmin-homepage-plugins-list">
        <ul>'. $plugins_content .'</ul>
    </section>
    <section class="tlp-pane-section">
        <a href="/plugins/pluginsadministration/" class="tlp-button-primary tlp-button-outline tlp-button-wide">'.$Language->getText('admin_main', 'manage_all_plugins').'</a>
    </section>
');

// Start output
$siteadmin = new \Tuleap\Admin\AdminPageRenderer();
$siteadmin->header($Language->getText('admin_main', 'title'));

global $feedback;
echo html_feedback_top($feedback);

echo site_admin_warnings();

echo '<div id="siteadmin-homepage-container">';
echo '<div class="siteadmin-homepage-column">';

if ($GLOBALS['sys_user_approval'] == 1 || $GLOBALS['sys_project_approval'] == 1) {
    echo '<div class="siteadmin-homepage-row">';

    if ($GLOBALS['sys_user_approval'] == 1) {
        $wUser->display();
    }

    if ($GLOBALS['sys_project_approval'] == 1) {
        $wProject->display();
    }

    echo '</div>';
}

echo '<div class="siteadmin-homepage-row">';
$wStats->display();
$wPlugins->display();
echo '</div>';
echo '</div>';

echo '<div class="siteadmin-homepage-column">';
echo '<section class="tlp-pane">
    <div class="tlp-pane-container">
        <div class="tlp-pane-header">
            <h1 class="tlp-pane-title"></h1>
        </div>
        <section class="tlp-pane-section">

        </section>
    </div>
</section>';
echo '</div>';
echo '</div>';

$GLOBALS['HTML']->footer(array());

<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
	
    $temp = new admin_settingpage('courseranker', 'Course Ranker');

    $temp->add(new admin_setting_configtext('courseranker/starttime', '���㿪ʼʱ��', '����γ̻�Ծ�ȵĿ�ʼʱ��', 0, PARAM_INT));
    
    $temp->add(new admin_setting_configcheckbox('courseranker/flush','��ջ��棿','������ģ��ʱ��ջ���',0));

    $ADMIN->add('localplugins', $temp);
}

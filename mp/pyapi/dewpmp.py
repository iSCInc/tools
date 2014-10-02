#! /usr/bin/env python
# -*- coding: utf-8 -*-

class DewpmpException(Exception):
    def __init__(self, msg):
        self.msg = msg

    def __str__(self):
        return repr(self.msg)

import oursql
import string

class Database:
    def __init__(self, user_name=None, password=None, host=None,
                 database=None, wp_database='dewiki_p'):
        """
        Constructor.

        *wp_database* may be `None`. Otherwise it should be the wiki’s database
        name, e. g. 'dewiki_p'.
        """
        # try to read the replica.mp-db.cnf
        import ConfigParser
        import os.path
        import mp_db_config

        p = mp_db_config.db_conf_file
        if os.path.exists(p):
            parser = ConfigParser.ConfigParser()
            parser.read(p)
            if parser.has_section('client'):
                if parser.has_option('client', 'user') and user_name is None:
                    user_name = string.strip(parser.get('client', 'user'), '"\'')
                if (parser.has_option('client', 'password') and password is None):
                    password = string.strip(parser.get('client', 'password'), '"\'')
                if parser.has_option('client', 'host') and host is None:
                    host = string.strip(parser.get('client', 'host'), '"\'')

        if user_name is None or password is None or host is None:
            raise DewpmpException(u'You did not specify enough information on' +
                                u' the database connection. The replica.mp-db.cnf ' +
                                u'file did not contain the required ' +
                                u'information. Used user_name: %s, host: %s' % (user_name, host))

        try:
            self.conn = oursql.connect(user=user_name, passwd=password, host=host, db=database)
            self.conn_wp = oursql.connect(user=user_name, passwd=password, host=host, db=wp_database,
                           charset='utf8', use_unicode=True)
        except oursql.DatabaseError, e:
            raise DewpmpException(u'You specified wrong database ' +
                                  u'connection data. Error message: ' + 
                                  unicode(e))

    def get_all_mentors(self):
        with self.conn as curs:
            curs.execute('''
            SELECT `mentor_user_id`, `mentor_user_name`,
                   `mentor_login_password`,
                   `mentor_in`, `mentor_out`,
                   `mentor_has_barnstar`, `mentor_award_level`,
                   `mentor_remark`, `mentor_lastupdate`
                FROM `mentor`
                ORDER BY `mentor_user_name`
            ;''')
            mentors_list = curs.fetchall()
            mentors_dict = []
            for item in mentors_list:
                mentors_dict.append({'user_id':item[0],
                                     'user_name':item[1],
                                     'login_password':item[2],
                                     'in':item[3],
                                     'out':item[4],
                                     'has_barnstar':item[5],
                                     'award_level':item[6],
                                     'remark':item[7],
                                     'lastupdate':item[8]})
            return mentors_dict

    def get_all_mentees(self, mentor_user_name=None, only_active=True):
        """
           returns a list of all current (='active') mentees for given mentor
        """
        if mentor_user_name != None:
           mentor_where_stm = u" AND `mentor_user_name` = '" + mentor_user_name + u"'"
        else:
           mentor_where_stm = u""

        with self.conn as curs:
            curs.execute('''
            SELECT `mm_type`, `mentor_user_id`, `mentor_user_name`,
                   `mentee_user_id`, `mentee_user_name`,                   
                   `mm_start`, `mm_stop`
                FROM `mentee`
                JOIN `mentee_mentor` ON `mentee_user_id`=`mm_mentee_id` AND `mm_stop` is '''
                + (u"not",u"")[only_active] +
                ''' NULL
                JOIN `mentor` ON `mentor_user_id`=`mm_mentor_id` '''
                + mentor_where_stm +
                '''
                WHERE `mentee_is_hidden`=0
                ORDER BY `mentee_user_name`
            ;''')
            mentees_list = curs.fetchall()
            mentees_dict = []
            for item in mentees_list:
                mentees_dict.append({'mentee_type':item[0],
                                'mentor_user_id':item[1],
                                'mentor_user_name':item[2],
                                'mentee_user_id':item[3],
                                'mentee_user_name':item[4],
                                'mm_start':item[5],
                                'mm_stop':item[6]})
            return mentees_dict

    def get_all_mentors_for_mentee(self, mentee_user_name):
        """
           returns a list of all mentors for given mentee
        """
        with self.conn as curs:
            curs.execute('''
            SELECT `mm_type`, `mentor_user_id`, `mentor_user_name`,
                   `mentee_user_id`, `mentee_user_name`,
                   `mm_start`, `mm_stop`
                FROM `mentee`
                JOIN `mentee_mentor` ON `mentee_user_id`=`mm_mentee_id` AND `mentee_user_name` = ?
                JOIN `mentor` ON `mentor_user_id`=`mm_mentor_id`
                WHERE `mentee_is_hidden`= 0
                ORDER BY `mentee_user_name`
            ;''',(mentee_user_name,))
            mentor_list = curs.fetchall()
            mentor_dict = []
            for item in mentor_list:
                mentor_dict.append({'mentee_type':item[0],
                                'mentor_user_id':item[1],
                                'mentor_user_name':item[2],
                                'mentee_user_id':item[3],
                                'mentee_user_name':item[4],
                                'mm_start':item[5],
                                'mm_stop':item[6]})
            return mentor_dict

    def add_mentee(self, mentor_name, mentee_name, mentoring_type=0, timestamp=None):
        """
           if mentee is already in:
                  'stops' the old mentoring, if not already done
                  'starts' a new mentoring with given mentor
           else:
                  'starts' a new mentoring with given mentor
                  
           returns if the operation was sucessful

           mentoring_type: 0=not set, 1=normal, 2=wunschmentor
        """
        mentee_user_id = self.get_mw_user_id(mentee_name)
        mentor_user_id = self.get_mw_user_id(mentor_name)
        if (mentee_user_id == None):
            raise DewpmpException(u'Coud not find ' + mentee_name + u' in wp_DB')
        if (mentor_user_id == None):
            raise DewpmpException(u'Coud not find mentor: ' + mentor_name + u' in wp_DB')

        self._touch_mentor(mentor_name, mentor_user_id)

        # find this user in our DB
        with self.conn as curs:
            curs.execute('''
            SELECT `mentee_user_id`, `mentee_user_name`
                FROM `mentee`
                WHERE `mentee_user_id` = ?
            ;''',(mentee_user_id,))

            row = curs.fetchone()
            if row != None:
               if (row[1] != mentee_name):
                  # this user was renamed in WP, update this item in our DB
                  self.rename_mentee(mentee_user_id, mentee_name)
                  return True # nothing more to do               
            else:
               # this user is unkown
               self.add_mentee_user(mentee_name, mentee_user_id)

        # is there an 'old mentoring' item? then close it
        self.stop_all_current_mentoring(mentee_user_id)

        # add a new mentoring item
        with self.conn as curs:
            if timestamp == None:
               curs.execute('''
               INSERT INTO `mentee_mentor`
                  (`mm_mentee_id`, `mm_mentor_id`,
                   `mm_start`, `mm_stop`, `mm_type`)
                  VALUE (?, ?, CURRENT_TIMESTAMP, NULL, ?)
               ;''',(mentee_user_id, mentor_user_id, mentoring_type,))
            else:
               curs.execute('''
               INSERT INTO `mentee_mentor`
                  (`mm_mentee_id`, `mm_mentor_id`,
                   `mm_start`, `mm_stop`, `mm_type`)
                  VALUE (?, ?, ?, NULL, ?)
               ;''',(mentee_user_id, mentor_user_id, timestamp, mentoring_type,))

    def add_mentee_user(self, mentee_name, mentee_id, timestamp=None):
        """
          This is only for adding a mentee without any mentor
        """
        with self.conn as curs:
            if timestamp == None:
               curs.execute('''
               INSERT INTO `mentee` (`mentee_user_id`,
                   `mentee_user_name` , `mentee_is_hidden`,
                   `mentee_remark` , `mentee_lastupdate` )
                   VALUES (?, ?, '0', NULL, CURRENT_TIMESTAMP)
               ;''', (mentee_id, mentee_name,))
            else:
               curs.execute('''
               INSERT INTO `mentee` (`mentee_user_id`,
                   `mentee_user_name` , `mentee_is_hidden`,
                   `mentee_remark` , `mentee_lastupdate` )
                   VALUES (?, ?, '0', NULL, ?)
               ;''', (mentee_id, mentee_name, timestamp,))
        return True

    def _touch_mentor(self, mentor_name, mentor_user_id):
        """
           Check if we already know the 'mentor' as a mentor
           Add it or rename it in our database if necessary
        """
        with self.conn as curs:
            curs.execute('''
            SELECT `mentor_user_id`, `mentor_user_name`
                FROM `mentor` WHERE `mentor_user_id` = ?
            ;''',(mentor_user_id,))
            row = curs.fetchone()
            if row == None:
               # user is unknow, add it
               with self.conn as curs:
                  curs.execute('''
                  INSERT INTO `mentor` (
                     `mentor_user_id`, `mentor_user_name`, `mentor_login_password`,
                     `mentor_in`, `mentor_out`,
                     `mentor_has_barnstar`, `mentor_award_level`,
                     `mentor_remark`, `mentor_lastupdate`) 
                      VALUES (?, ?, NULL , CURRENT_TIMESTAMP, NULL , 0, 0, NULL , CURRENT_TIMESTAMP)
                      ;''', (mentor_user_id, mentor_name))
            elif (row[1] != mentor_name):
               # we know the id, but not the name
               # -> the user was renamed, update our datebase
               with self.conn as curs:
                  curs.execute('''
                  UPDATE `mentor`
                     SET `mentor_user_name` =  ? 
                     WHERE `mentor_user_id` = ? LIMIT 1
                      ;''', (mentor_name, mentor_user_id,))               
 

    def rename_mentee(self, mentee_id, new_mentee_name):
        with self.conn as curs:
            curs.execute('''
            UPDATE `mentee`
                SET `mentee_user_name` =  ?  
                WHERE `mentee`.`mentee_user_id` =  ?  LIMIT 1
            ;''', (new_mentee_name, mentee_id,))
        return True

    def stop_all_current_mentoring(self, mentee_id, timestamp = None):
        with self.conn as curs:
            if (timestamp == None):
               curs.execute('''
               UPDATE `mentee_mentor`
                   SET `mm_stop` = CURRENT_TIMESTAMP
                   WHERE `mm_stop` is NULL AND `mm_mentee_id` = ?
               ;''', (mentee_id,))
            else:
               curs.execute('''
               UPDATE `mentee_mentor`
                   SET `mm_stop` = ?
                   WHERE `mm_stop` is NULL AND `mm_mentee_id` = ?
               ;''', (timestamp, mentee_id,))

    def get_overall_mentee_number(self):
        """
        Returns the number of mentees ever been in WP:MP.
        """
        if self.conn == None:
            return False

        with self.conn as curs:
            curs.execute('''SELECT COUNT(`mentee_user_id`) FROM `mentee`;''')
            row = curs.fetchone()
            if row != None and row[0] != None:
                return int(row[0])
            else:
                return None
    
    def get_active_mentor_number(self):
        """
        Returns the number of mentors that are active and have at least one mentee.
        """
        if self.conn == None:
            return False

        with self.conn as curs:
            curs.execute('''SELECT COUNT( DISTINCT mentor_user_id ) FROM `mentor`
            JOIN mentee_mentor ON mentee_mentor.mm_mentor_id = mentor.mentor_user_id
            WHERE `mentor_out` IS NULL AND mm_stop IS NULL;''')
            row = curs.fetchone()
            if row != None and row[0] != None:
                return int(row[0])
            else:
                return None

    # XXX not used
    def get_mentee_by_id(self, mentee_id):
        with self.conn as curs:
            curs.execute('''
            SELECT `mentee_user_id`, `mentee_user_name`,
                   `mentee_is_hidden`, `mentee_remark`,
                   `mentee_lastupdate`
                FROM `mentee`
                WHERE `mentee_id` = ?
            ;''', (mentee_id,))
            if curs.rowcount > 0:
                item = curs.fetchone()
                return {'id':item[0],
                        'user_id':item[1],
                        'user_name':item[2],
                        'remark':item[13],
                        'lastupdate':item[14]}
            else:
                return None

###############
# queries for the de_wikip database
###############

    def _fixUTF8problem(self, string):
        return string
        """
           this is a workaround, because we don't get correct utf-8 back from dewiki_p
           maybe there is a better way?
        """
        mapping = ((u"\xc3\u0178", u"ß"), (u'\xc3\xbc', u'ü'), (u'\xc3\xb6', u'ö'), (u'\xc3\xa4', u'ä'),
                   (u"\xc3\xa9", u"é"), (u"\xc3\u2013", u"Ö"))
        for m in mapping:
            string = string.replace(m[0], m[1])
        return string

    def get_mw_user_id(self, user_name):
        """
        Returns the MediaWiki user id for the user *user_name* or `None` if
        the user does not exist.
        """
        if self.conn_wp == None:
            return False

        with self.conn_wp as curs:
            curs.execute('''
            SELECT `user_id` FROM `user`
                WHERE `user_name` = CONVERT(CAST(? AS BINARY) USING latin1)
            ;''',(user_name,))
            row = curs.fetchone()
            if row != None and row[0] != None:
                return int(row[0])
            else:
                return None

    def get_mw_cat_members(self, cat_name):
        """
        Returns all users in given category
        """
        with self.conn_wp as curs:
            curs.execute('''
            SELECT CONVERT(CAST(`page_title` as BINARY) USING utf8)
                FROM `page`
                  JOIN `categorylinks` ON cl_to = ? AND `cl_from`=`page_id` AND `cl_type`="page"
                WHERE `page_namespace`=2 ORDER BY `page_title`
            ;''',(cat_name,))
            m_list = curs.fetchall()
            m_dict = []
            for item in m_list:
                m_dict.append({'item': self._fixUTF8problem(item[0])})
            return m_dict

    def get_mw_user_contribsum(self, user_id, latest_days):
        """
        Returns the number of latest contributions for user with given id.
        We look only throught the 'latest_days' days (today == 0).
        This method is used to identify inactive users.
        """
        with self.conn_wp as curs:
            curs.execute(u'''
              select COUNT(rev_id) from revision_userindex join page on (page_id = rev_page) where 
              rev_user=? and DATEDIFF(NOW(), rev_timestamp) < ?
            ;''',(user_id, latest_days,))
            row = curs.fetchone()
            if row != None and row[0] != None:
                return int(row[0])
            else:
                return None


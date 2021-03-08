DROP TABLE IF EXISTS MemberDemographics;

CREATE TABLE MemberDemographics (
  user_id        int(11),
  gov_type       text,
  gov_number     text,
  head_house     int(11),
  surname        text,
  forename       text,
  mid_name       text,
  birth_date     DATE,
  email_addesss  text,
  race           tinyint(3) unsigned DEFAULT '0',
  ethnicity      tinyint(3) unsigned DEFAULT '0',
  disability     ENUM('yes','no'),
  pregnant       ENUM('yes','no'),
  marital_status ENUM('married','single','unknown',''),
  caregiver      ENUM('yes','no'),
  student        ENUM('full','part','no'),
  relationship   ENUM('H','S','A','C','F',''),
  house_mems     tinyint(3) unsigned DEFAULT '0',
  t_stamp        TIMESTAMP,  
  KEY user_id (user_id),
  KEY head (head_house),
  KEY name (surname(10),forename(10))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/**
 * @signature 399e7443a6e7f329a765603bc226208a
 * @version 1.8.0
 *
 * Migrate to a single attachment table to allow for inline image support
 * with an almost countless number of attachment tables to support what is
 * attached to what
 */

DROP TABLE IF EXISTS `%TABLE_PREFIX%attachment`;
CREATE TABLE `%TABLE_PREFIX%attachment` (
  `object_id` int(11) unsigned NOT NULL,
  `type` char(1) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `inline` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`file_id`,`type`)
) DEFAULT CHARSET=utf8;

-- Migrate canned attachments
INSERT INTO `%TABLE_PREFIX%attachment`
  (`object_id`, `type`, `file_id`, `inline`)
  SELECT `canned_id`, 'C', `file_id`, 0
  FROM `%TABLE_PREFIX%canned_attachment`;

DROP TABLE `%TABLE_PREFIX%canned_attachment`;

-- Migrate faq attachments
INSERT INTO `%TABLE_PREFIX%attachment`
  (`object_id`, `type`, `file_id`, `inline`)
  SELECT `faq_id`, 'F', `file_id`, 0
  FROM `%TABLE_PREFIX%faq_attachment`;

DROP TABLE `%TABLE_PREFIX%faq_attachment`;

-- Migrate email templates to HTML
UPDATE `%TABLE_PREFIX%email_template`
  SET `body` = REPLACE('\n', '<br/>',
    REPLACE('&', '&amp;',
        REPLACE('<', '&lt;',
            REPLACE('>', '%gt;', `body`))));

-- Finished with patch
UPDATE `%TABLE_PREFIX%config`
    SET `value` = '399e7443a6e7f329a765603bc226208a'
    WHERE `key` = 'schema_signature' AND `namespace` = 'core';

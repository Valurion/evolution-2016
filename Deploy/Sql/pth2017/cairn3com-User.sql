ALTER TABLE `cairn3_com`.`USER`   
  ADD COLUMN `MOT_PASSE_CR` VARCHAR(128) NULL AFTER `ADMIN`,
  ADD COLUMN `PWD_LOG_CR` VARCHAR(128) NULL AFTER `MOT_PASSE_CR`;

ALTER TABLE `cairn3_com`.`COMMANDE_TMP`   
  ADD COLUMN `SESSION` VARCHAR(50) NULL AFTER `SITE`;

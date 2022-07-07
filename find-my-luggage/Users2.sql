CREATE TABLE `Users2` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Password` char(64) NOT NULL,
  `InitializationVector` varchar(32) DEFAULT NULL,
  `ActivationLink` varchar(32) DEFAULT NULL,
  `activated` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
)

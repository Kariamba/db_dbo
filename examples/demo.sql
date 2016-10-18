DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `companyID` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `mail` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `company`  ADD PRIMARY KEY (`companyID`);
	
INSERT INTO `company` (`companyID`, `name`, `phone`, `mail`) VALUES
(1, 'My Company 1', '+7 (999) 000-00-01', 'somemail@company1.test'),
(2, 'My Company 2', '+7 (999) 000-00-02', 'somemail@company2.test'),
(3, 'My Company 3', '+7 (999) 000-00-03', 'somemail@company3.test');

-- --------------------------------------------------------

DROP TABLE IF EXISTS `company_office`;
CREATE TABLE `company_office` (
  `officeID` int(11) NOT NULL,
  `companyID` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `company_office` ADD PRIMARY KEY (`officeID`);

INSERT INTO `company_office` (`officeID`, `companyID`, `name`, `address`) VALUES
(1, 1, 'Office 1', 'Office address 1');

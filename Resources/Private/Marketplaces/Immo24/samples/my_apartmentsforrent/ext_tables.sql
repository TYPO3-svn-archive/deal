#
# Table structure for table 'tx_my_apartmentsforrent'
#
CREATE TABLE tx_my_apartmentsforrent (
  # #i0036, 150825, dwildt, +
	immo24id int(11) NOT NULL DEFAULT '0',
	immo24idSandbox int(11) NOT NULL DEFAULT '0',
	immo24log text,
	immo24tstamp int(11) NOT NULL DEFAULT '0',
	immo24tstampSandbox int(11) NOT NULL DEFAULT '0',
	immo24url varchar(255) DEFAULT '',
	immo24urlSandbox varchar(255) DEFAULT '',
);

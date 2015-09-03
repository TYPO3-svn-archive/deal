# INDEX
# -----
# tx_deal_ebaycategories
# tx_deal_ebaycategories_000
# tx_deal_ebaycategories_077
# tx_deal_ebayshippingservicecode
# tx_deal_ebayshippingservicecode_000
# tx_deal_ebayshippingservicecode_077
# tx_deal_immo24certificate
# tx_deal_immo24certificateSandbox
# tx_quickshop_products
# tx_quickshop_products_mm_tx_deal_ebaycategories
# tx_quickshop_products_mm_tx_deal_ebaycategories_000
# tx_quickshop_products_mm_tx_deal_ebaycategories_077
# tx_quickshop_products_mm_tx_deal_ebayshippingservicecode
# tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_000
# tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_077



#
# Table structure for table 'tx_deal_ebaycategories'
#
CREATE TABLE tx_deal_ebaycategories (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  uid_parent int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_ebaycategories_000'
#
CREATE TABLE tx_deal_ebaycategories_000 (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  uid_parent int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_ebaycategories_077'
#
CREATE TABLE tx_deal_ebaycategories_077 (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  uid_parent int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_ebayshippingservicecode'
#
CREATE TABLE tx_deal_ebayshippingservicecode (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  code tinytext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_ebayshippingservicecode_000'
#
CREATE TABLE tx_deal_ebayshippingservicecode_000 (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  code tinytext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_ebayshippingservicecode_077'
#
CREATE TABLE tx_deal_ebayshippingservicecode_077 (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext,
  code tinytext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_deal_immo24certificate'
#
CREATE TABLE tx_deal_immo24certificate (
  uid int(11) DEFAULT '0' NOT NULL,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  ic_id int(16) unsigned NOT NULL auto_increment,
  ic_desc varchar(32) DEFAULT '0' NOT NULL,
  ic_expire datetime DEFAULT NULL,
  ic_key varchar(128) DEFAULT '0' NOT NULL,
  ic_secret varchar(128) DEFAULT '0' NOT NULL,
  ic_username varchar(60) DEFAULT NULL,
  PRIMARY KEY (ic_id),
  KEY parent (pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Table structure for table 'tx_deal_immo24certificate'
#
CREATE TABLE tx_deal_immo24certificateSandbox (
  uid int(11) DEFAULT '0' NOT NULL,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  ic_id int(16) unsigned NOT NULL auto_increment,
  ic_desc varchar(32) DEFAULT '0' NOT NULL,
  ic_expire datetime DEFAULT NULL,
  ic_key varchar(128) DEFAULT '0' NOT NULL,
  ic_secret varchar(128) DEFAULT '0' NOT NULL,
  ic_username varchar(60) DEFAULT NULL,
  PRIMARY KEY (ic_id),
  KEY parent (pid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

#
# Table structure for table 'tx_quickshop_products'
#
CREATE TABLE tx_quickshop_products (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tx_deal_ebayaction tinytext,
  tx_deal_ebaycategoryid tinytext,
  tx_deal_ebayconditionid tinytext,
  tx_deal_ebaydispatchtimemax tinytext,
  tx_deal_ebayexternalLinks tinytext,
  tx_deal_ebayitemid tinytext,
  tx_deal_ebayitemstatus tinytext,
  tx_deal_ebaylistingduration tinytext,
  tx_deal_ebaylocation tinytext,
  tx_deal_ebaymode tinytext,
  tx_deal_ebaypaymentmethods tinytext,
  tx_deal_ebaypaymentmethodsdescription tinytext,
  tx_deal_ebayquantity tinytext,
  tx_deal_ebaylog text,
  tx_deal_ebayreturnsacceptoption tinytext,
  tx_deal_ebayreturnpolicydescription tinytext,
  tx_deal_ebayshippingserviceadditionalcosts tinytext,
  tx_deal_ebayshippingservicecode tinytext,
  tx_deal_ebayshippingservicecosts tinytext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebaycategories'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebaycategories (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebaycategories_000'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebaycategories_000 (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebaycategories_077'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebaycategories_077 (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebayshippingservicecode'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebayshippingservicecode (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_000'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_000 (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_077'
#
CREATE TABLE tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_077 (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

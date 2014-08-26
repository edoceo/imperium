
-- Base Data for the System

insert into auth_user (id,username,password,active) values (1,'root','root','t');
insert into auth_user (id,username,password,active) values (2,'demo','demo','t');

-- Base Accounts

INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (1,'true','Business Checking', NULL, 0.00, NULL, NULL, 69904, 100, '100', 'Asset', '100 - Asset:Business Checking', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (2,'true','Business Savings', NULL, 0.00, NULL, NULL, 69888, 110, '110', 'Asset', '110 - Asset:Business Savings', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (3,'true','Miscellaneous Liabilities', NULL, 0.00, NULL, NULL, 135168, 200, '200', 'Liability', '200 - Liability:Miscellaneous Liabilities', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (4,'true','Sales Taxes', NULL, 0.00, NULL, NULL, 139264, 220, '220', 'Liability', '220 - Liability:Sales Taxes', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (5,'true','Income Summary', NULL, 0.00, NULL, NULL, 73728, 191, '191', 'Asset', '191 - Asset:Income Summary', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (6,'true','Accounts Receivable', NULL, 0.00, 200, NULL, 70656, 130, '130', 'Asset', '130 - Asset:Accounts Receivable', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (7,'true','Inbound Cash', NULL, 0.00, NULL, NULL, 73728, 190, '190', 'Asset', '190 - Asset:Inbound Cash', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (8,'true','Owners Capital', NULL, 0.00, NULL, NULL, 266240, 510, '510', 'Equity', '510 - Equity:Owners Capital', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (9,'true','Miscellaneous Revenue', NULL, 0.00, NULL, NULL, 1056768, 390, '390', 'Revenue', '390 - Revenue:Miscellaneous Revenue', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (10,'true','Interest Revenue', NULL, 0.00, NULL, NULL, 1056768, 350, '350', 'Revenue', '350 - Revenue:Interest Revenue', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (11,'true','Invoice Revenue', NULL, 0.00, 300, NULL, 1056768, 300, '300', 'Revenue', '300 - Revenue:Invoice Revenue', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (12,'true','Miscellaneous', 21, 0.00, NULL, NULL, 2101248, 900, '420.900', 'Expense', '420.900 - Expense:Miscellaneous', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (13,'true','Development Services', NULL, 0.00, NULL, NULL, 2101248, 460, '460', 'Expense', '460 - Expense:Development Services', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (14,'true','Developers', 84, 0.00, NULL, NULL, 2101248, 100, '460.100', 'Expense', '460.100 - Expense:Developers', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (15,'true','Miscellaneous', 17, 0.00, NULL, NULL, 2101248, 900, '450.900', 'Expense', '450.900 - Expense:Miscellaneous', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (16,'true','Communications Services', NULL, 0.00, NULL, NULL, 2105344, 430, '430', 'Expense', '430 - Expense:Communications Services', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (17,'true','Travel and Transportation', NULL, 0.00, NULL, NULL, 2105344, 450, '450', 'Expense', '450 - Expense:Travel and Transportation', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (18,'true','Business Services', NULL, 0.00, NULL, NULL, 2105344, 400, '400', 'Expense', '400 - Expense:Business Services', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (19,'true','Internet Services', NULL, 0.00, NULL, NULL, 2105344, 420, '420', 'Expense', '420 - Expense:Internet Services', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (20,'true','Miscellaneous', 19, 0.00, NULL, NULL, 2101248, 900, '400.900', 'Expense', '400.900 - Expense:Miscellaneous', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (21,'true','Equipment', NULL, 0.00, NULL, NULL, 2105344, 401, '401', 'Expense', '401 - Expense:Equipment', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (22,'true','Meals', 17, 0.00, NULL, NULL, 2105344, 300, '450.300', 'Expense', '450.300 - Expense:Meals', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (23,'true','Auto and Fuel', 17, 0.00, NULL, NULL, 2101248, 200, '450.200', 'Expense', '450.200 - Expense:Auto and Fuel', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (24,'true','Advertising', 19, 0.00, NULL, NULL, 2105344, 300, '400.300', 'Expense', '400.300 - Expense:Advertising', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (25,'true','Parking', 17, 0.00, NULL, NULL, 2101248, 100, '450.100', 'Expense', '450.100 - Expense:Parking', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (26,'true','Miscellaneous Expense', NULL, 91.00, NULL, NULL, 2105344, 490, '490', 'Expense', '490 - Expense:Miscellaneous Expense', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (27,'true','Misc', 2, NULL, NULL, NULL, 70656, 999, '130.999', 'Asset', '130.999 - Asset:Misc', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (28,'true','Client Ledgers', 2, NULL, NULL, NULL, 70656, 600, '600', null, '600 - Sub: Client Ledgers', NULL);
INSERT INTO account (id,active, name, parent_id, balance, link_to, link_id, flag, code, full_code, kind, full_name, account_tax_line_id) VALUES (29,'true','Vendor Ledgers', 2, NULL, NULL, NULL, 70656, 700, '700', null, '700 - Sub: Vendor Ledgers', NULL);

select setval('account_id_seq',(select id from account order by id desc limit 1),true);

INSERT INTO base_enum (link,name,sort) VALUES ('contact-kind','Contact',100);
INSERT INTO base_enum (link,name,sort) VALUES ('contact-kind','Company',200);
INSERT INTO base_enum (link,name,sort) VALUES ('contact-kind','Vendor',300);

INSERT INTO base_enum (link,name,sort) VALUES ('contact-status','Active',100);
INSERT INTO base_enum (link,name,sort) VALUES ('contact-status','Archive',400);

INSERT INTO base_enum (link,name,sort) VALUES ('invoice-kind','Active',100);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-kind','Archive',400);

INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Active',100);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Sent',201);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Hawk',300);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Loss',400);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Paid',600);
-- INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Archive',400);
-- INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Trade',400);
INSERT INTO base_enum (link,name,sort) VALUES ('invoice-status','Void',900);

INSERT INTO base_enum (link,name,sort) VALUES ('workorder-kind','Single',100);
INSERT INTO base_enum (link,name,sort) VALUES ('workorder-kind','Project',200);
INSERT INTO base_enum (link,name,sort) VALUES ('workorder-kind','Monthly',300);
INSERT INTO base_enum (link,name,sort) VALUES ('workorder-kind','Quarterly',400);
INSERT INTO base_enum (link,name,sort) VALUES ('workorder-kind','Yearly',500);

INSERT INTO base_enum (link,name,sort) VALUES ('workorder-item-status','Active',100);
INSERT INTO base_enum (link,name,sort) VALUES ('workorder-item-status','Complete',200);
--
-- Base Object Descriptors
--

-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (100, 'Account', 'account', NULL, 'account', 'account');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (102, 'AccountLedgerEntry', 'accountledgerentry', NULL, 'accountledgerentry', 'account_ledger_entry');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (150, 'AccountWizardJournal', 'accountwizardjournal', NULL, 'accountwizardjournal', 'account_wizard_journal');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (151, 'AccountWizardLedgerEntry', 'accountwizardledgerentry', NULL, 'accountwizardledgerentry', 'account_wizard_ledger');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (6564, 'ImperiumUser', 'imperiumuser', NULL, 'imperiumuser', 'auth_user');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (300, 'Invoice', 'invoice', NULL, 'invoice', 'invoice');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (6170, 'Manual', 'manual', NULL, 'manual', NULL);
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (9802, 'Timesheet', 'timesheet', NULL, 'timesheet', 'timesheet');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (301, 'InvoiceItem', 'invoiceitem', NULL, 'invoice.item/view/id/%d', 'invoice_item');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (101, 'AccountJournalEntry', 'accountjournalentry', NULL, 'account/transaction/view/id/%d', 'account_journal');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (201, 'ContactAddress', 'contactaddress', NULL, 'contact/address?id=%d', 'contact_address');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (202, 'ContactChannel', 'contactchannel', NULL, 'contact.channel/view/id/%d', 'contact_channel');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (401, 'WorkOrderItem', 'workorderitem', NULL, 'workorder.item/view/id/%d', 'workorder_item');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (600, 'Note', 'note', NULL, 'note/view/id/%d', 'base_note');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (5005, 'File', 'file', 'Files can be attached to any other object', 'file/view/id/%d', 'base_file');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (8750, 'Todo', 'todo', NULL, 'todo/view/id/%d', 'base_todo');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (400, 'WorkOrder', 'workorder', NULL, 'workorder/view/id/%d', 'workorder');
-- INSERT INTO base_object (id, name, stub, note, path, link) VALUES (200, 'Contact', 'contact', NULL, 'contact/view/id/%d', 'contact');

-- Units of Measurement for WorkOrder or Invoice Items
INSERT INTO base_unit (id, name) VALUES ('ea','Each');
INSERT INTO base_unit (id, name) VALUES ('ft','Foot');
INSERT INTO base_unit (id, name) VALUES ('g','Gram');
INSERT INTO base_unit (id, name) VALUES ('GiB','Gigabyte');
INSERT INTO base_unit (id, name) VALUES ('hr','Hour');
INSERT INTO base_unit (id, name) VALUES ('km','Kilometer');
INSERT INTO base_unit (id, name) VALUES ('lb','Pound');
INSERT INTO base_unit (id, name) VALUES ('MiB','Megabyte');
INSERT INTO base_unit (id, name) VALUES ('m','Meter');
INSERT INTO base_unit (id, name) VALUES ('mi','Mile');
INSERT INTO base_unit (id, name) VALUES ('mo','Month');
INSERT INTO base_unit (id, name) VALUES ('oz','Ounce');
INSERT INTO base_unit (id, name) VALUES ('qt','Quarter');
INSERT INTO base_unit (id, name) VALUES ('yd','Yard');
INSERT INTO base_unit (id, name) VALUES ('yr','Year');

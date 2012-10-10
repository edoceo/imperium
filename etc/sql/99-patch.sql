--
-- @file
-- @brief Random Patches

ALTER TABLE account_period RENAME column a_date to date_alpha;
ALTER TABLE account_period RENAME column z_date to date_omega;

ALTER TABLE workorder ADD COLUMN kind varchar(32);
UPDATE workorder SET kind = 'Single' WHERE kind_id = 100;
UPDATE workorder SET kind = 'Project' WHERE kind_id = 200;
UPDATE workorder SET kind = 'Subscription' WHERE kind_id = 300;
ALTER TABLE worKorder DROP COLUMN kind_id;

-- New Enum Stuff
ALTER TABLE base_enum drop column code;
CREATE SEQUENCE base_enum_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER SEQUENCE base_enum_id_seq OWNED BY base_enum.id;

BEGIN;
INSERT INTO base_enum (id,link,name,sort) VALUES (1,'workorder','Single',100);
INSERT INTO base_enum (id,link,name,sort) VALUES (2,'workorder','Project',200);
INSERT INTO base_enum (id,link,name,sort) VALUES (3,'workorder','Monthly',300);
INSERT INTO base_enum (id,link,name,sort) VALUES (4,'workorder','Quarterly',400);
INSERT INTO base_enum (id,link,name,sort) VALUES (5,'workorder','Yearly',500);
COMMIT;

-- Migrate WorkOrder to Invoice
begin;
alter table invoice add column kind varchar(64);
alter table invoice add column base_rate numeric(5,2);
alter table invoice add column base_unit varchar(64);
alter table invoice add column requester varchar(128);

alter table invoice_item add column date date;
alter table invoice_item add column kind varchar(32);
commit;

-- rename columns
begin;
alter table workorder drop column requestor_id;
alter table workorder rename column requestor to requester;
commit;

--
begin;
update invoice set status = 'Active' where status = 'Open';
commit;

alter table invoice_item drop CONSTRAINT fk_invoice_id;
alter table invoice_item add constraint fk_invoice_id foreign key (invoice_id) references invoice(id) INITIALLY DEFERRED;

alter table workorder_item add column status varchar(32);
update workorder_item set status = 'Pending' where status_id = 1000;
update workorder_item set status = 'Active' where status_id = 2000;
update workorder_item set status = 'Complete' where status_id = 3000;
update workorder_item set status = 'Billed' where status_id = 4000;
drop view workorder_item_invoice_item;
alter table workorder_item drop column status_id;

alter table base_file rename column colour to star;
alter table base_note rename column status to star;
alter table base_note drop column colour;

alter table invoice rename column colour to star;
alter table workorder rename column colour to star;

alter table workorder_item drop column notify;
alter table workorder_item drop column datetime;

alter table invoice rename column description to note;
alter table invoice_item rename column description to note;
alter table workorder rename column description to note;
alter table workorder_item rename column description to note;

alter table workorder alter column note type text;
alter table invoice alter column note type text;

begin;
drop view general_ledger;
alter table account_journal drop column account_period_id;
create view general_ledger AS  SELECT a.id AS account_id, c.id AS account_journal_id, b.id AS account_ledger_id, a.code AS account_code, a.full_code AS account_full_code, a.name AS account_name, a.full_name AS account_full_name, c.date, b.amount, c.kind, c.note, b.link_to, b.link_id
   FROM account a
   JOIN account_ledger b ON a.id = b.account_id
   JOIN account_journal c ON c.id = b.account_journal_id;

commit;

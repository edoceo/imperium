--
-- @file
-- @brief Random Patches

DROP VIEW general_ledger;
DROP VIEW workorder_item_invoice_item;

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

--
begin;
update invoice set status = 'Active' where status = 'Open';
commit;

-- Modify Constraints
alter table invoice drop CONSTRAINT fk_invoice_contact_id;
alter table invoice add constraint fk_invoice_contact_id foreign key (contact_id) references contact(id) INITIALLY DEFERRED;

alter table invoice_item drop CONSTRAINT fk_invoice_id;
alter table invoice_item add constraint fk_invoice_id foreign key (invoice_id) references invoice(id) INITIALLY DEFERRED;

alter table workorder drop CONSTRAINT fk_work_order_contact_id;
alter table workorder add constraint fk_workorder_contact_id foreign key (contact_id) references contact(id) INITIALLY DEFERRED;

alter table base_file rename column colour to star;
alter table base_note rename column status to star;
alter table base_note drop column colour;

alter table invoice rename column colour to star;
alter table workorder rename column colour to star;

alter table workorder_item drop column notify;
alter table workorder_item drop column datetime;
alter table workorder_item drop percent_complete;

alter table invoice rename column description to note;
alter table invoice_item rename column description to note;
alter table workorder rename column description to note;
alter table workorder_item rename column description to note;

alter table workorder alter column note type text;
alter table invoice alter column note type text;

-- Text Columns for Everything Now
alter table contact alter column contact type text;
alter table contact alter column company type text;
alter table contact alter column name type text;
alter table contact alter column sound_code type text;
alter table contact alter column phone type text;
alter table contact alter column email type text;
alter table contact alter column url type text;
alter table contact alter column title type text;
alter table contact alter column spouse type text;
alter table contact alter column tags type text;
alter table contact alter column first_name type text;
alter table contact alter column last_name type text;

alter table workorder alter column requester type text;
alter table workorder alter column location type text;

alter table workorder_item alter column name type text;

alter table invoice_item alter column name type text;

alter table account alter column note type text;
alter table account_ledger alter column note type text;
alter table account_journal alter column note type text;

-- alter table account add column contact_id int null references contact(id);
alter table contact add column account_id int null references account(id);

begin;
ALTER TABLE workorder_item ADD COLUMN time_alpha time without time zone;
ALTER TABLE workorder_item ADD COLUMN time_omega time without time zone;
commit;
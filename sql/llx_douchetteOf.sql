CREATE TABLE llx_douchetteOf
(
    rowid INT PRIMARY KEY NOT NULL,
    fk_of varchar(100) NOT NULL,
    fk_post varchar(100) NOT NULL,
    fk_user INT NOT NULL,
    total_time INT,
    datec datetime,
    fk_statut INT NOT NULL
)
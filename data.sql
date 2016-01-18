-- id - номер овцы
-- yard_id - загона для овец
-- alive - живая или нет
-- сtime - время рождения овцы
-- atime - время последнего изменения овцы

CREATE TABLE public.funny_farm (
  id serial PRIMARY KEY NOT NULL,
  yard_id integer NOT NULL,
  alive boolean NOT NULL DEFAULT true,
  ctime timestamp without time zone NOT NULL DEFAULT now(),
  atime timestamp without time zone NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX unique_id ON funny_farm USING BTREE (id);

INSERT INTO public.funny_farm (yard_id, alive, ctime, atime) VALUES (1, TRUE, NOW(), NOW());
INSERT INTO public.funny_farm (yard_id, alive, ctime, atime) VALUES (1, TRUE, NOW(), NOW());
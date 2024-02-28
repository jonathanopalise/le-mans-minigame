CC = m68k-atarimegabrowner-elf-gcc
CFLAGS = -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fleading-underscore -g -Os -fomit-frame-pointer -m68000 -Wl,--traditional-format -Wall
VASM = vasmm68k_mot
VASM_OPTS = -no-opt
VLINK = vlink
PHP = php

OBJECT_FILES =\
	src/lemans.o\
	src/game_loop.o\
	src/hardware_playfield.o\
    src/draw_sprite.o\
	src/vbl_handler.o\
    src/road_movement.o\
    src/movement_update_inner.o\
    src/track_segments.o\
    src/trackside_items.o\
    src/mountains_render.o\
    src/road_render.o\
    src/player_car.o\
	src/generated/road_geometry.o\
	src/generated/road_graphics.o\
    src/generated/mountain_graphics.o\
    src/generated/sprite_definitions.o\
	src/initialise.o

ASSETS_GIF = assets/round-tree.gif

bin/lemans.prg: $(OBJECT_FILES)
	$(CC) -o src/lemans.elf libcxx/brownboot.o libcxx/browncrti.o libcxx/browncrtn.o libcxx/browncrt++.o libcxx/zerolibc.o libcxx/zerocrtfini.o $(OBJECT_FILES) -g -Os -Wl,--emit-relocs -Wl,-e_start -Ttext=0 -nostartfiles -m68000 -fomit-frame-pointer -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fstrict-aliasing -fcaller-saves -ffunction-sections -fdata-sections -fleading-underscore
	brownout -s -i src/lemans.elf -o bin/lemans.prg
	chmod +x bin/lemans.prg

src/lemans.o: src/lemans.c $(OBJECT_FILES)
	$(CC) $(CFLAGS) -c src/lemans.c -o src/lemans.o

src/game_loop.o: src/game_loop.c src/game_loop.h src/hardware_playfield.h src/initialise.h src/vbl_handler.h src/road_movement.h src/mountains_render.h src/road_render.h src/player_car.h src/sprite_definitions.h src/road_geometry.h
	$(CC) $(CFLAGS) -c src/game_loop.c -o src/game_loop.o

src/hardware_playfield.o: src/hardware_playfield.c src/hardware_playfield.h src/initialise.h src/vbl_handler.h src/road_render.h src/sprite_definitions.h
	$(CC) $(CFLAGS) -c src/hardware_playfield.c -o src/hardware_playfield.o

src/draw_sprite.o: src/draw_sprite.s src/draw_sprite.h
	$(VASM) $(VASM_OPTS) src/draw_sprite.s -Felf -o src/draw_sprite.o

src/vbl_handler.o: src/vbl_handler.c src/vbl_handler.h
	$(CC) $(CFLAGS) -c src/vbl_handler.c -o src/vbl_handler.o

src/road_movement.o: src/road_movement.c src/road_movement.h src/road_geometry.h src/player_car.h src/movement_update_inner.h
	$(CC) $(CFLAGS) -c src/road_movement.c -o src/road_movement.o

src/movement_update_inner.o: src/movement_update_inner.s src/movement_update_inner.h
	$(VASM) $(VASM_OPTS) src/movement_update_inner.s -Felf -o src/movement_update_inner.o

src/track_segments.o: src/track_segments.c src/track_segments.h
	$(CC) $(CFLAGS) -c src/track_segments.c -o src/track_segments.o

src/trackside_items.o: src/trackside_items.c src/trackside_items.h
	$(CC) $(CFLAGS) -c src/trackside_items.c -o src/trackside_items.o

src/mountains_render.o: src/mountains_render.c src/mountains_render.h src/mountain_graphics.h src/hardware_playfield.h src/blitter.h
	$(CC) $(CFLAGS) -c src/mountains_render.c -o src/mountains_render.o

src/road_render.o: src/road_render.c src/road_render.h src/road_graphics.h src/road_geometry.h src/hardware_playfield.h src/blitter.h src/player_car.h
	$(CC) $(CFLAGS) -c src/road_render.c -o src/road_render.o

src/player_car.o: src/player_car.c src/player_car.h src/track_segments.h src/initialise.h
	$(CC) $(CFLAGS) -c src/player_car.c -o src/player_car.o

src/generated/road_geometry.o: src/generated/road_geometry.c src/road_geometry.h
	$(CC) $(CFLAGS) -c src/generated/road_geometry.c -o src/generated/road_geometry.o

src/generated/road_geometry.c: src/generate_road_geometry.php
	$(PHP) src/generate_road_geometry.php src/generated/road_geometry.c

src/generated/road_graphics.o: src/generated/road_graphics.c src/road_graphics.h
	$(CC) $(CFLAGS) -c src/generated/road_graphics.c -o src/generated/road_graphics.o

src/generated/road_graphics.c: src/generate_road_graphics.php
	$(PHP) src/generate_road_graphics.php src/generated/road_graphics.c

src/generated/mountain_graphics.o: src/generated/mountain_graphics.c src/mountain_graphics.h
	$(CC) $(CFLAGS) -c src/generated/mountain_graphics.c -o src/generated/mountain_graphics.o

src/generated/mountain_graphics.c: src/generate_mountain_graphics.php assets/mountains.gif
	$(PHP) src/generate_mountain_graphics.php assets/mountains.gif src/generated/mountain_graphics.c

src/generated/sprite_definitions.o: src/generated/sprite_definitions.c src/sprite_definitions.h
	$(CC) $(CFLAGS) -c src/generated/sprite_definitions.c -o src/generated/sprite_definitions.o

src/generated/sprite_definitions.c: src/generate_sprite_definitions.php $(ASSETS_GIF) src/sprite_definitions_template.php src/library.php
	$(PHP) src/generate_sprite_definitions.php $(ASSETS_GIF) src/generated/sprite_definitions.c

src/initialise.o: src/initialise.s
	$(VASM) $(VASM_OPTS) src/initialise.s -Felf -o src/initialise.o


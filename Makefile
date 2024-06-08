CC = m68k-atarimegabrowner-elf-gcc
CFLAGS = -nostdlib -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -flto -fleading-underscore -Os -fomit-frame-pointer -m68000 -Wall
VASM = vasmm68k_mot
VASM_OPTS =
VLINK = vlink
PHP = php

OBJECT_FILES =\
	src/lemans.o\
	src/game_loop.o\
	src/hardware_playfield.o\
    src/draw_sprite.o\
    src/draw_status.o\
	src/vbl_handler.o\
    src/road_movement.o\
    src/movement_update_inner.o\
    src/track_segments.o\
    src/trackside_items_process.o\
    src/mountains_render.o\
    src/road_render.o\
    src/road_render_fast.o\
    src/player_car.o\
    src/opponent_cars.o\
    src/display_list.o\
    src/detect_collisions.o\
    src/random.o\
    src/hud.o\
    src/time_of_day_process.o\
    src/relocate_sprites.o\
    src/checkpoints.o\
    src/natfeats.o\
    src/generated/trackside_items.o\
 	src/generated/road_geometry.o\
	src/generated/road_graphics.o\
    src/generated/mountain_graphics.o\
    src/generated/sprite_definitions.o\
    src/generated/status_definitions.o\
    src/generated/time_of_day.o\
	src/initialise.o\
	src/nf_asmv.o\
	src/mixer_init.o\
	src/mixer_data.o\
	src/mixer_variables.o\
	src/mixer_vbl.o\
	src/play_sound.o\

ASSETS_GIF = assets/round-tree.gif

bin/lemans.prg: $(OBJECT_FILES)
	$(CC)  -o src/lemans.elf libcxx/vsnprint.o libcxx/brownboot.o libcxx/zerolibc.o libcxx/browncrti.o libcxx/browncrtn.o libcxx/browncrt++.o libcxx/zerocrtfini.o $(OBJECT_FILES)  -Os -Wl,--emit-relocs -Wl,-e_start -Ttext=0 -nostartfiles -m68000 -fomit-frame-pointer -flto -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fstrict-aliasing -fcaller-saves -ffunction-sections -fdata-sections -fleading-underscore
	brownout -x -i src/lemans.elf -o bin/lemans.prg
	chmod +x bin/lemans.prg

src/lemans.o: src/lemans.c $(OBJECT_FILES)
	$(CC) $(CFLAGS) -c src/lemans.c -o src/lemans.o

src/game_loop.o: src/game_loop.c src/game_loop.h src/hardware_playfield.h src/initialise.h src/vbl_handler.h src/road_movement.h src/mountains_render.h src/road_render.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/trackside_items.h src/display_list.h src/detect_collisions.h src/opponent_cars.h src/time_of_day_process.h src/detect_collisions.h src/mixer_init.h src/hud.h src/natfeats.h
	$(CC) $(CFLAGS) -c src/game_loop.c -o src/game_loop.o

src/hardware_playfield.o: src/hardware_playfield.c src/hardware_playfield.h src/blitter.h src/draw_sprite.h src/draw_status.h src/status_definitions.h src/bitplane_draw_record.h src/natfeats.h src/initialise.h src/hud.h src/hud_digits.h
	$(CC) $(CFLAGS) -c src/hardware_playfield.c -o src/hardware_playfield.o

src/draw_sprite.o: src/draw_sprite.s src/draw_sprite.h
	$(VASM) $(VASM_OPTS) src/draw_sprite.s -Felf -o src/draw_sprite.o

src/draw_status.o: src/draw_status.s src/draw_status.h
	$(VASM) $(VASM_OPTS) src/draw_status.s -Felf -o src/draw_status.o

src/vbl_handler.o: src/vbl_handler.c src/vbl_handler.h
	$(CC) $(CFLAGS) -c src/vbl_handler.c -o src/vbl_handler.o

src/road_movement.o: src/road_movement.c src/road_movement.h src/road_geometry.h src/player_car.h src/movement_update_inner.h src/trackside_items.h src/trackside_items_process.h src/play_sound.h
	$(CC) $(CFLAGS) -c src/road_movement.c -o src/road_movement.o

src/movement_update_inner.o: src/movement_update_inner.s src/movement_update_inner.h
	$(VASM) $(VASM_OPTS) src/movement_update_inner.s -Felf -o src/movement_update_inner.o

src/track_segments.o: src/track_segments.c src/track_segments.h
	$(CC) $(CFLAGS) -c src/track_segments.c -o src/track_segments.o

src/trackside_items_process.o: src/trackside_items_process.c src/trackside_items_process.h src/trackside_items.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/trackside_items.h src/display_list.h
	$(CC) $(CFLAGS) -c src/trackside_items_process.c -o src/trackside_items_process.o

src/mountains_render.o: src/mountains_render.c src/mountains_render.h src/mountain_graphics.h src/hardware_playfield.h src/blitter.h src/road_movement.h
	$(CC) $(CFLAGS) -c src/mountains_render.c -o src/mountains_render.o

src/road_render.o: src/road_render.c src/road_render.h src/road_graphics.h src/road_geometry.h src/hardware_playfield.h src/blitter.h src/player_car.h src/road_render_fast.h src/player_car.h src/checkpoints.h
	$(CC) $(CFLAGS) -c src/road_render.c -o src/road_render.o

src/road_render_fast.o: src/road_render_fast.s src/road_render_fast.h src/road_graphics.h src/road_geometry.h src/hardware_playfield.h src/blitter.h
	$(VASM) $(VASM_OPTS) src/road_render_fast.s -Felf -o src/road_render_fast.o

src/player_car.o: src/player_car.c src/player_car.h src/track_segments.h src/initialise.h src/hardware_playfield.h src/checkpoints.h src/hud.h src/play_sound.h
	$(CC) $(CFLAGS) -c src/player_car.c -o src/player_car.o

src/opponent_cars.o: src/opponent_cars.c src/opponent_cars.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/display_list.h src/random.h
	$(CC) $(CFLAGS) -c src/opponent_cars.c -o src/opponent_cars.o

src/display_list.o: src/display_list.c src/display_list.h src/sprite_definitions.h src/hardware_playfield.h
	$(CC) $(CFLAGS) -c src/display_list.c -o src/display_list.o

src/detect_collisions.o: src/detect_collisions.c src/detect_collisions.h src/player_car.h src/trackside_items_process.h src/trackside_items.h src/road_geometry.h src/opponent_cars.h src/play_sound.h
	$(CC) $(CFLAGS) -c src/detect_collisions.c -o src/detect_collisions.o

src/random.o: src/random.c src/random.h
	$(CC) $(CFLAGS) -c src/random.c -o src/random.o

src/hud.o: src/hud.c src/hud.h src/hud_digits.h
	$(CC) $(CFLAGS) -c src/hud.c -o src/hud.o

src/time_of_day_process.o: src/time_of_day_process.c src/time_of_day_process.h
	$(CC) $(CFLAGS) -c src/time_of_day_process.c -o src/time_of_day_process.o

src/relocate_sprites.o: src/relocate_sprites.c src/relocate_sprites.h src/generated/sprite_definitions_count.h
	$(CC) $(CFLAGS) -c src/relocate_sprites.c -o src/relocate_sprites.o

src/checkpoints.o: src/checkpoints.c src/checkpoints.h
	$(CC) $(CFLAGS) -c src/checkpoints.c -o src/checkpoints.o

src/natfeats.o: src/natfeats.c src/natfeats.h
	$(CC) $(CFLAGS) -c src/natfeats.c -o src/natfeats.o

src/generated/trackside_items.c: src/generate_trackside_items.php src/trackside_items_template.php
	$(PHP) src/generate_trackside_items.php src/generated/trackside_items.c

src/generated/trackside_items.o: src/generated/trackside_items.c src/trackside_items.h
	$(CC) $(CFLAGS) -c src/generated/trackside_items.c -o src/generated/trackside_items.o

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

src/generated/mountain_graphics.c: src/generate_mountain_graphics.php src/library.php assets/mountains.gif
	$(PHP) src/generate_mountain_graphics.php assets/mountains.gif src/generated/mountain_graphics.c

src/generated/sprite_definitions.o: src/generated/sprite_definitions.c src/sprite_definitions.h
	$(CC) $(CFLAGS) -c src/generated/sprite_definitions.c -o src/generated/sprite_definitions.o

src/generated/sprite_definitions.c: src/generate_sprite_definitions.php src/library.php src/sprite_spans.php $(ASSETS_GIF) src/sprite_definitions_template.php src/library.php src/sprite_definitions.php
	$(PHP) src/generate_sprite_definitions.php $(ASSETS_GIF) src/generated/sprite_definitions.c

src/generated/sprite_definitions_count.h: src/generate_sprite_definitions_count.php src/sprite_definitions.php
	$(PHP) src/generate_sprite_definitions_count.php src/generated/sprite_definitions_count.h

src/generated/status_definitions.o: src/generated/status_definitions.c src/status_definitions.h
	$(CC) $(CFLAGS) -c src/generated/status_definitions.c -o src/generated/status_definitions.o

src/generated/status_definitions.c: src/generate_status_definitions.php $(ASSETS_GIF) src/status_definitions_template.php src/library.php
	$(PHP) src/generate_status_definitions.php $(ASSETS_GIF) src/generated/status_definitions.c

src/generated/time_of_day.o: src/generated/time_of_day.c src/time_of_day.h
	$(CC) $(CFLAGS) -c src/generated/time_of_day.c -o src/generated/time_of_day.o

src/generated/time_of_day.c: src/generate_time_of_day.php
	$(PHP) src/generate_time_of_day.php src/generated/time_of_day.c

src/initialise.o: src/initialise.s
	$(VASM) $(VASM_OPTS) src/initialise.s -Felf -o src/initialise.o

src/mixer_init.o: src/mixer_init.s src/mixer_init.h
	$(VASM) $(VASM_OPTS) src/mixer_init.s -Felf -o src/mixer_init.o

src/mixer_data.o: src/mixer_data.s
	$(VASM) $(VASM_OPTS) src/mixer_data.s -Felf -o src/mixer_data.o

src/mixer_variables.o: src/mixer_variables.s
	$(VASM) $(VASM_OPTS) src/mixer_variables.s -Felf -o src/mixer_variables.o

src/mixer_vbl.o: src/mixer_vbl.s
	$(VASM) $(VASM_OPTS) src/mixer_vbl.s -Felf -o src/mixer_vbl.o

src/play_sound.o: src/play_sound.s
	$(VASM) $(VASM_OPTS) src/play_sound.s -Felf -o src/play_sound.o

src/nf_asmv.o: src/nf_asmv.s
	$(VASM) $(VASM_OPTS) src/nf_asmv.s -Felf -o src/nf_asmv.o


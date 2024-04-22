    public _movement_update_inner_scenario_2
    public _movement_update_inner_scenario_1
    public _movement_update_inner_scenario_3

_movement_update_inner_scenario_1:

    move.l a2,-(sp)
    move.l sp,a0

    ; should be in this order with sp going upwards:
    ;            sizeof(struct RoadScanline),                                        // needs to go into a2
    ;            &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
    ;           &(current_road_scanline->logical_xpos_add_values[shift_required]   // needs to go into a1

    move 10(a0),a2      ; sizeof(struct RoadScanline)
    move.l 16(a0),a1
    move.l 12(a0),a0

    rept 100
    move.l (a1),d0     ; current_road_scanline->logical_xpos_add_values[shift_required]
    add.l d0,(a0)      ; current_logical_xpos+=(above value)
    add.l a2,a0          ; increment by sizeof(RoadScanline)
    add.l a2,a1          ; increment by sizeof(RoadScanline)
    endr

    move.l (sp)+,a2
    
    rts

_movement_update_inner_scenario_2:

    move.l a2,-(sp)
    move.l sp,a0

    ; should be in this order with sp going upwards:
    ;            sizeof(struct RoadScanline),                                        // needs to go into a2
    ;            &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
    ;           &(current_road_scanline->logical_xpos_add_values[shift_required]   // needs to go into a1

    move 10(a0),a2      ; sizeof(struct RoadScanline)
    move.l 16(a0),a1
    move.l 12(a0),a0

    rept 100
    move.l (a1),d0     ; current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l d0,(a0)      ; current_logical_xpos+=(above value)
    add.l a2,a0          ; increment by sizeof(RoadScanline)
    add.l a2,a1          ; increment by sizeof(RoadScanline)
    endr
    move.l (sp)+,a2

    rts

_movement_update_inner_scenario_3:

    ; don't forget to preserve registers

    rts

    move.l a3,-(sp)

    move 6(a0),a3      ; sizeof(struct RoadScanline)
    move.l 8(a0),a2    ; &(current_road_scanline->current_logical_xpos),
    move.l 12(a0),a1   ; &(current_road_scanline->logical_xpos_add_values[-shift_required])
    move.l 16(a0),a0   ; &(current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply])

    rept 100
    ; get current logical xpos
    move.l (a2),d0
    add.l (a1),d0
    add.l (a0),d0
    move.l d0,(a2)
    add.l a3,a2 ; 8 cycles
    add.l a3,a1 ; 8 cycles
    add.l a3,a0 ; 8 cycles

    endr

    move.l (sp)+,a3

    rts



    public _movement_update_inner_scenario_2
    public _movement_update_inner_scenario_1
    public _movement_update_inner_scenario_3
    public _movement_update_inner_scenario_4
    public _movement_update_inner_scenario_5
    public _movement_update_inner_scenario_6

_movement_update_inner_scenario_1:

    ; should be in this order with sp going upwards:
    ;            sizeof(struct RoadScanline),                                        // needs to go into a2
    ;            &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
    ;           &(current_road_scanline->logical_xpos_add_values[shift_required]   // needs to go into a1

    move 6(sp),a1       ; sizeof(struct RoadScanline)
    move.l 8(sp),a0     ; &(current_road_scanline->current_logical_xpos)
    move.l 12(sp),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l a0,d1

    rept 100
    move.l 0(a0,d1),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    add.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    rts

_movement_update_inner_scenario_2:

    move 6(sp),a1       ; sizeof(struct RoadScanline)
    move.l 8(sp),a0     ; &(current_road_scanline->current_logical_xpos)
    move.l 12(sp),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l a0,d1

    rept 100
    move.l 0(a0,d1),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    rts

_movement_update_inner_scenario_3:

    move.l sp,a0
    move.l d2,-(sp)

    move 6(a0),a1       ; sizeof(struct RoadScanline)
    move.l 12(a0),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    move.l 16(a0),d2    ; total change
    move.l 8(a0),a0     ; &(current_road_scanline->current_logical_xpos)
    sub.l a0,d1
    sub.l a0,d2

    rept 100
    move.l 0(a0,d1),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    add.l 0(a0,d2),d0
    add.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    move.l (sp)+,d2

    rts

_movement_update_inner_scenario_4:

    move.l sp,a0
    move.l d2,-(sp)

    move 6(a0),a1       ; sizeof(struct RoadScanline)
    move.l 12(a0),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    move.l 16(a0),d2    ; total change
    move.l 8(a0),a0     ; &(current_road_scanline->current_logical_xpos)
    sub.l a0,d1
    sub.l a0,d2

    rept 100
    move.l 0(a0,d1),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l 0(a0,d2),d0
    add.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    move.l (sp)+,d2

    rts

_movement_update_inner_scenario_5:

    move.l sp,a0
    move.l d2,-(sp)

    move 6(a0),a1       ; sizeof(struct RoadScanline)
    move.l 12(a0),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    move.l 16(a0),d2    ; total change
    move.l 8(a0),a0     ; &(current_road_scanline->current_logical_xpos)
    sub.l a0,d1
    sub.l a0,d2

    rept 100
    move.l 0(a0,d2),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l 0(a0,d1),d0
    add.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    move.l (sp)+,d2

    rts

_movement_update_inner_scenario_6:

    move.l sp,a0
    move.l d2,-(sp)

    move 6(a0),a1       ; sizeof(struct RoadScanline)
    move.l 12(a0),d1    ; &(current_road_scanline->logical_xpos_add_values[shift_required]
    move.l 16(a0),d2    ; total change
    move.l 8(a0),a0     ; &(current_road_scanline->current_logical_xpos)
    sub.l a0,d1
    sub.l a0,d2

    rept 100
    move.l 0(a0,d1),d0   ; current_road_scanline->logical_xpos_add_values[shift_required]
    add.l 0(a0,d2),d0
    sub.l d0,(a0)        ; current_logical_xpos+=(above value)
    add.l a1,a0          ; increment by sizeof(RoadScanline)
    endr

    move.l (sp)+,d2

    rts


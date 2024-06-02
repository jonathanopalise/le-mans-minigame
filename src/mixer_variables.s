    public bufferAudioMixer
    public addressAudioCurrentStart
    public addressAudioCurrentEnd
    public addressAudioWorkingStart
    public addressAudioWorkingEnd
    public variableEngineEffectPosition
    public variableSoundEventLatch
    public variableSoundEventAddress
    public variableSoundEventLength
    public variableSoundEventRetrigPeriod
    public variableSoundEventPosition
    public hasDmaSound


; --- variables used by mixer
bufferAudioMixer					ds.b		500

    align 1
addressAudioCurrentStart			ds.l		1
addressAudioCurrentEnd				ds.l		1
addressAudioWorkingStart			ds.l		1
addressAudioWorkingEnd				ds.l		1

variableEngineEffectPosition		ds.w		1
variableSoundEventLatch				ds.w		1
variableSoundEventAddress			ds.l		1
variableSoundEventLength			ds.w		1
variableSoundEventRetrigPeriod		ds.w		1
variableSoundEventPosition			ds.w		1

hasDmaSound                         ds.w        1

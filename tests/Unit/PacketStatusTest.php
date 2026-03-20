<?php

use App\Enums\PacketStatus;

//  TRANSICIONES VÁLIDAS

it('permite la transición de created a in_transit', function () {
    expect(PacketStatus::Created->canTransitionTo(PacketStatus::InTransit))->toBeTrue();
});

it('permite la transición de in_transit a delivered', function () {
    expect(PacketStatus::InTransit->canTransitionTo(PacketStatus::Delivered))->toBeTrue();
});

it('permite la transición de in_transit a failed', function () {
    expect(PacketStatus::InTransit->canTransitionTo(PacketStatus::Failed))->toBeTrue();
});


//  TRANSICIONES INVÁLIDAS

it('no permite saltar de created a delivered', function () {
    expect(PacketStatus::Created->canTransitionTo(PacketStatus::Delivered))->toBeFalse();
});

it('no permite saltar de created a failed', function () {
    expect(PacketStatus::Created->canTransitionTo(PacketStatus::Failed))->toBeFalse();
});

it('no permite ninguna transición desde delivered', function () {
    expect(PacketStatus::Delivered->canTransitionTo(PacketStatus::Created))->toBeFalse();
    expect(PacketStatus::Delivered->canTransitionTo(PacketStatus::InTransit))->toBeFalse();
    expect(PacketStatus::Delivered->canTransitionTo(PacketStatus::Failed))->toBeFalse();
});

it('no permite ninguna transición desde failed', function () {
    expect(PacketStatus::Failed->canTransitionTo(PacketStatus::Created))->toBeFalse();
    expect(PacketStatus::Failed->canTransitionTo(PacketStatus::InTransit))->toBeFalse();
    expect(PacketStatus::Failed->canTransitionTo(PacketStatus::Delivered))->toBeFalse();
});

it('los estados terminales no tienen transiciones permitidas', function () {
    expect(PacketStatus::Delivered->allowedTransitions())->toBeEmpty();
    expect(PacketStatus::Failed->allowedTransitions())->toBeEmpty();
});
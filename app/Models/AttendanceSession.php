<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'class_section_id', 'session_date', 'start_time', 'end_time', 'topic',
    'status', 'late_after_minutes', 'created_by',
])]
class AttendanceSession extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'status' => SessionStatus::class,
            'late_after_minutes' => 'integer',
            'qr_expires_at' => 'datetime',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        return $this->status === SessionStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === SessionStatus::Closed;
    }

    /**
     * The wall-clock moment this session begins, combining date and time.
     */
    public function startsAt(): CarbonInterface
    {
        return Carbon::parse(
            $this->session_date->format('Y-m-d').' '.substr((string) $this->start_time, 0, 8)
        );
    }

    public function endsAt(): CarbonInterface
    {
        return Carbon::parse(
            $this->session_date->format('Y-m-d').' '.substr((string) $this->end_time, 0, 8)
        );
    }

    /**
     * Arrivals after this moment are recorded as late rather than present.
     */
    public function lateThreshold(): CarbonInterface
    {
        return $this->startsAt()->addMinutes($this->late_after_minutes);
    }

    /**
     * The status a check-in right now would produce.
     */
    public function statusForCheckInAt(?CarbonInterface $moment = null): AttendanceStatus
    {
        $moment ??= now();

        return $moment->greaterThan($this->lateThreshold())
            ? AttendanceStatus::Late
            : AttendanceStatus::Present;
    }

    public function qrIsValid(): bool
    {
        return $this->qr_token !== null
            && $this->qr_expires_at !== null
            && $this->qr_expires_at->isFuture();
    }

    public function secondsUntilQrExpires(): int
    {
        if (! $this->qrIsValid()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->qr_expires_at, false));
    }

    public function timeRange(): string
    {
        return substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5);
    }

    public function title(): string
    {
        return $this->session_date->format('D, d M Y').' · '.$this->timeRange();
    }

    /** @param  Builder<AttendanceSession>  $query */
    public function scopeClosed(Builder $query): void
    {
        $query->where('status', SessionStatus::Closed);
    }

    /** @param  Builder<AttendanceSession>  $query */
    public function scopeOnDate(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('session_date', $date);
    }

    /** @param  Builder<AttendanceSession>  $query */
    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): void
    {
        $query->when($from, fn (Builder $q) => $q->whereDate('session_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('session_date', '<=', $to));
    }
}

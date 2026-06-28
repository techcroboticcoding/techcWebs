class StudentProgress extends Model
{
    protected $fillable = [

        'student_id',
        'progress',
        'level',
        'status',
        'teacher_note'

    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
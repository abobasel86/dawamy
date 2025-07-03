@extends('layouts.admin') {{-- أو أي layout تستخدمه --}}

@section('content')
<div class="container">
    <h1>تقرير العمل الإضافي المستحق</h1>
    <p>هذا التقرير يعرض جميع طلبات العمل الإضافي التي تمت الموافقة عليها.</p>

    {{-- قسم الفلاتر --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('finance.overtime.report') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="month" class="form-label">اختر الشهر</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ $selectedMonth }}">
                </div>
                <div class="col-md-4">
                    <label for="user_id" class="form-label">اختر الموظف (اختياري)</label>
                    <select name="user_id" id="user_id" class="form-control">
                        <option value="">جميع الموظفين</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($selectedUser == $employee->id)>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('finance.overtime.report') }}" class="btn btn-secondary">إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول النتائج --}}
    <div class="card">
        <div class="card-header">
            نتائج شهر: {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}
            {{-- زر تصدير وهمي حالياً --}}
            <button class="btn btn-success btn-sm float-end">تصدير إلى Excel</button>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الموظف</th>
                        <th>التاريخ</th>
                        <th>نوع اليوم</th>
                        <th>الساعات الفعلية</th>
                        <th>معدل الحساب</th>
                        <th>📈 الساعات المستحقة للدفع</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPayableHours = 0; @endphp
                    @forelse($overtimeRecords as $record)
                        @php
                            $isHoliday = \App\Models\OfficialHoliday::where('date', $record->date)->exists();
                            $totalPayableHours += $record->payable_hours;
                        @endphp
                        <tr>
                            <td>{{ $record->user->name }}</td>
                            <td>{{ $record->date }}</td>
                            <td>
                                @if($isHoliday)
                                    <span class="badge bg-info">عطلة رسمية</span>
                                @else
                                    <span class="badge bg-secondary">يوم عمل</span>
                                @endif
                            </td>
                            <td>{{ round($record->actual_minutes / 60, 2) }} ساعة</td>
                            <td>
                                @if($isHoliday)
                                    2.0x
                                @else
                                    1.5x
                                @endif
                            </td>
                            <td><strong>{{ $record->payable_hours }} ساعة</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد سجلات تطابق هذا البحث.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="5" class="text-end"><strong>الإجمالي الكلي للساعات المستحقة:</strong></td>
                        <td><strong>{{ $totalPayableHours }} ساعة</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
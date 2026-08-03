@component('mail::message')
# Weekly progress report — {{ $klantName }}

Here is how your cursisten are progressing this week.

@if (count($cursisten) > 0)
@component('mail::table')
| Cursist | Assigned | In progress | Completed |
| :--- | :---: | :---: | :---: |
@foreach ($cursisten as $cursist)
| {{ $cursist->name }} | {{ $cursist->assignedCount }} | {{ $cursist->inProgressCount }} | {{ $cursist->completedCount }} |
@endforeach
@endcomponent
@else
No cursisten have any assignments yet.
@endif

Thanks,<br>
{{ $klantName }}
@endcomponent

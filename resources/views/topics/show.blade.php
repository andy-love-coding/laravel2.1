@extends('layouts.app')

@section('title', $topic->title)
@section('description', $topic->excerpt)

@section('content')

  <div class="row">

    <div class="col-lg-3 col-md-3 hidden-sm hidden-xs author-info">
      <div class="card">
        <div class="card-body">
          <div class="text-center">
            作者：{{ $topic->name }}
          </div>
          <hr>
          <div class="media">
            <div align="center">
              <a href="{{ route('users.show', $topic->user_id) }}">
                <img src="{{ $topic->user->avatar }}" class="thumbnail img-fluid" widht="300px" height="300px">
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-9 com-md-9 col-sm-12 col-xs-12 topic-content">
      <div class="card">
        <div class="card-body">
          <h1 class="text-center mt-3 mb-3">
            {{ $topic->title }}
          </h1>

          <div class="article-meta text-center text-secondary">
            {{ $topic->created_at->diffFOrHumans() }}
            ⋅
            <i class="far fa-comment"></i>
            {{ $topic->reply_count }}
          </div>

          <div class="topic-body mt-4 mb-4">
            {!! $topic->body !!}
          </div>

          <div class="operate">
            <hr>
            <a href="{{ route('topics.edit', $topic->id) }}" class="btn btn-outline-secondary btn-sm" role="button">
              <i class="far fa-edit"></i> 编辑
            </a>
            <a href="#" class="btn btn-outline-secondary btn-sm" role="button">
              <i class="far fa-trash-alt"></i> 删除
            </a>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection

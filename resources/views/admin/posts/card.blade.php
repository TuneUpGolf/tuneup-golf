<div id="blog" class="bg-gray-100 px-4 xl:px-4 py-14">
    <div class="mx-auto container">
        <div class="focus:outline-none mt-5 mb-5 lg:mt-24">
            <div class="infinity">
                <div class="flex flex-col justify-center items-center w-100">
                    @php $model = $model->paginate(6); @endphp
                    @each('admin.posts.blog', $model, 'post')
                    {{ $model->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

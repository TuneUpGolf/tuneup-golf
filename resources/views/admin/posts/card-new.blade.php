<div class="">
    <div class="focus:outline-none mt-3 mb-3 lg:mt-24">
        <div class="infinity">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
                @if ($posts->count() > 0)
                    @each('admin.posts.blog', $posts, 'post')
                @else
                    <p class="text-gray-500 text-lg mt-5 col-span-3 text-center">No posts available.</p>
                @endif
            </div>

            {{-- Pagination centered --}}
            <div class="mt-6 flex justify-center">
                {{ $posts->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

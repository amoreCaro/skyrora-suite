import { useState, useEffect } from 'react';
import { DndContext, closestCenter, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy, arrayMove, useSortable } from '@dnd-kit/sortable';

export const Object = ({ attributes, index, setAttributes }) => {
    const [posts, setPosts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [selectedPosts, setSelectedPosts] = useState(attributes.news || []);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');
    const [isLoaded, setIsLoaded] = useState(false);

    const categoryOptions = categories.map(category => ({
        label: category.name,
        value: category.name
    }));

    useEffect(() => {
        // Fetch posts
        fetch(`${wpApiSettings.root}wp/v2/${index}?_embed`)
            .then(response => response.json())
            .then(data => {
                const postsWithImages = data.map(post => ({
                    ...post,
                    image_url: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '/wp-content/uploads/2023/01/woocommerce-placeholder.png',
                }));
                setPosts(postsWithImages);
            })
            .catch(err => console.error('Error fetching posts:', err));

        // Fetch categories
        fetch(`${wpApiSettings.root}wp/v2/categories`)
            .then(response => response.json())
            .then(data => setCategories(data))
            .catch(err => console.error('Error fetching categories:', err));
    }, [index]);

    useEffect(() => {
        const timeout = setTimeout(() => {
            setIsLoaded(true);
        }, 1000);
        return () => clearTimeout(timeout);
    }, []);

    const handlePostSelect = (post) => {
        if (!selectedPosts.find(selected => selected.id === post.id)) {
            const updatedSelectedPosts = [...selectedPosts, post];
            setSelectedPosts(updatedSelectedPosts);
            setAttributes({ ...attributes, news: updatedSelectedPosts });
        }
    };

    const handlePostRemove = (postId) => {
        const updatedSelectedPosts = selectedPosts.filter(post => post.id !== postId);
        setSelectedPosts(updatedSelectedPosts);
        setAttributes({ ...attributes, news: updatedSelectedPosts });
    };

    const filteredPosts = posts.filter(post => {
        const matchesSearch = post.title.rendered.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesCategory = selectedCategory ? post.categories.includes(Number(selectedCategory)) : true;
        return matchesSearch && matchesCategory;
    });

    const isSelected = (postId) => selectedPosts.some(post => post.id === postId);

    const handleDragEnd = (event) => {
        const { active, over } = event;
        if (!over || active.id === over.id) return;

        const oldIndex = selectedPosts.findIndex(post => post.id === active.id);
        const newIndex = selectedPosts.findIndex(post => post.id === over.id);
        const updatedPosts = arrayMove(selectedPosts, oldIndex, newIndex);

        setSelectedPosts(updatedPosts);
        setAttributes({ ...attributes, news: updatedPosts });
    };

    const sensors = useSensors(useSensor(PointerSensor));

    return (
        <>
            {isLoaded ? (
                <div className="object">
                    <div className="object__nav">
                        <input
                            type="text"
                            placeholder="Search posts..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        <div className="object__select">
                            <select
                                name="object"
                                onChange={(e) => setSelectedCategory(e.target.value)}
                                value={selectedCategory}
                            >
                                {categories.map(category => (
                                    <option key={category.id} value={category.id}>
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="object__wrapper">
                        <div className="object__list">
                            {filteredPosts.length > 0 ? (
                                filteredPosts.map(post => (
                                    <div className="posts-grid" key={post.id}>
                                        <div
                                            className={`post-item ${isSelected(post.id) ? 'selected' : ''}`}
                                            onClick={() => handlePostSelect(post)}
                                        >
                                            <img height="50px" width="50px" src={post.image_url} alt="post" />
                                            <h3>{post.title.rendered}</h3>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="posts-grid posts-grid--empty">
                                    <p>No posts found.</p>
                                </div>
                            )}
                        </div>
                        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                            <SortableContext items={selectedPosts.map(post => post.id)} strategy={verticalListSortingStrategy}>
                                <div id="draggable-panel" className="object__selected">
                                    {selectedPosts.length > 0 ? (
                                        selectedPosts.map((post, index) => (
                                            <SortablePostItem
                                                key={post.id}
                                                id={post.id}
                                                post={post}
                                                onRemove={() => handlePostRemove(post.id)}
                                            />
                                        ))
                                    ) : (
                                        <span>No posts selected</span>
                                    )}
                                </div>
                            </SortableContext>
                        </DndContext>
                    </div>
                </div>
            ) : (
                <div className="loader__wrapper">
                    <div className="loader"></div>
                </div>
            )}
        </>
    );
};

const SortablePostItem = ({ id, post, onRemove }) => {
    const { attributes, listeners, setNodeRef, transform, transition } = useSortable({ id });
    const style = {
        transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
        transition,
    };

    return (
        <div ref={setNodeRef} style={style} className="post-item" {...attributes} {...listeners}>
            <img height="50px" width="50px" src={post.image_url} alt={post.alt} />
            <h4>{post.title.rendered}</h4>
            <button className="object__selected-btn" onClick={onRemove}>
                Delete
            </button>
        </div>
    );
};

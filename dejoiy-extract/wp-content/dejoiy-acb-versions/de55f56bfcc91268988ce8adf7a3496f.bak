<?php
/**
 * DEJOIY Nexus — public-domain catalog (Project Gutenberg).
 *
 * All titles are free, legal public-domain editions from Project Gutenberg.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $title  Book title.
 * @param string $author Author name.
 * @param int    $gid    Project Gutenberg ebook ID.
 * @param string $blurb  Short description.
 * @return array{title: string, author: string, gid: int, blurb: string}
 */
function dejoiy_library_catalog_entry( $title, $author, $gid, $blurb = '' ) {
	return array(
		'title'  => (string) $title,
		'author' => (string) $author,
		'gid'    => (int) $gid,
		'blurb'  => (string) $blurb,
	);
}

/**
 * @return array<string, array{label: string, books: array<int, array>}>
 */
function dejoiy_library_get_catalog() {
	return array(
		'business'          => array(
			'label' => 'Business',
			'books' => array(
				dejoiy_library_catalog_entry( 'The Art of War', 'Sun Tzu', 132, 'Ancient strategy for leadership and competition.' ),
				dejoiy_library_catalog_entry( 'The Prince', 'Niccolò Machiavelli', 1232, 'Classic treatise on power, politics, and leadership.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Benjamin Franklin', 'Benjamin Franklin', 148, 'Founding father on industry, thrift, and public life.' ),
				dejoiy_library_catalog_entry( 'Meditations', 'Marcus Aurelius', 2680, 'Stoic reflections on duty, discipline, and leadership.' ),
				dejoiy_library_catalog_entry( 'Essays on Political Economy', 'Frédéric Bastiat', 3560, 'Clear essays on markets, law, and economic freedom.' ),
				dejoiy_library_catalog_entry( 'The Wealth of Nations (Books I–III)', 'Adam Smith', 3300, 'Foundational work on markets, labor, and trade.' ),
				dejoiy_library_catalog_entry( 'Extraordinary Popular Delusions', 'Charles Mackay', 24518, 'History of crowd psychology and financial manias.' ),
				dejoiy_library_catalog_entry( 'The Art of Money Getting', 'P. T. Barnum', 514, 'Practical 19th-century advice on enterprise and reputation.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Andrew Carnegie', 'Andrew Carnegie', 17976, 'Industrialist on work, wealth, and philanthropy.' ),
				dejoiy_library_catalog_entry( 'On Liberty', 'John Stuart Mill', 34901, 'Essential argument for individual freedom and open debate.' ),
			),
		),
		'psychology'        => array(
			'label' => 'Psychology',
			'books' => array(
				dejoiy_library_catalog_entry( 'The Interpretation of Dreams', 'Sigmund Freud', 345, 'Landmark study of dreams and the unconscious mind.' ),
				dejoiy_library_catalog_entry( 'The Crowd: A Study of the Popular Mind', 'Gustave Le Bon', 445, 'Early classic on group behavior and mass psychology.' ),
				dejoiy_library_catalog_entry( 'Dream Psychology', 'Sigmund Freud', 15489, 'Accessible introduction to Freudian dream analysis.' ),
				dejoiy_library_catalog_entry( 'The Practice of Autosuggestion', 'Émile Coué', 5692, 'Mind–body methods for habit and self-direction.' ),
				dejoiy_library_catalog_entry( 'Thus Spoke Zarathustra', 'Friedrich Nietzsche', 1998, 'Philosophical exploration of mind, will, and meaning.' ),
				dejoiy_library_catalog_entry( 'Beyond Good and Evil', 'Friedrich Nietzsche', 16232, 'Challenge to moral convention and fixed thinking.' ),
				dejoiy_library_catalog_entry( 'The Expression of the Emotions in Man and Animals', 'Charles Darwin', 4363, 'Scientific study of emotion and expression.' ),
				dejoiy_library_catalog_entry( 'The Varieties of Religious Experience', 'William James', 401, 'Groundbreaking psychology of belief and experience.' ),
				dejoiy_library_catalog_entry( 'Character and the Conduct of Life', 'John Dewey', 11439, 'Pragmatic psychology of character and choice.' ),
				dejoiy_library_catalog_entry( 'The Analysis of Mind', 'Bertrand Russell', 2529, 'Early 20th-century inquiry into consciousness and thought.' ),
			),
		),
		'self-growth'       => array(
			'label' => 'Self Growth',
			'books' => array(
				dejoiy_library_catalog_entry( 'As a Man Thinketh', 'James Allen', 4507, 'Short classic on thought, habit, and personal mastery.' ),
				dejoiy_library_catalog_entry( 'The Science of Being Great', 'Wallace D. Wattles', 6550, 'Principles of purpose, confidence, and growth.' ),
				dejoiy_library_catalog_entry( 'Acres of Diamonds', 'Russell H. Conwell', 368, 'Famous lecture on opportunity and self-reliance.' ),
				dejoiy_library_catalog_entry( 'On the Shortness of Life', 'Seneca', 4276, 'Stoic essay on time, focus, and living well.' ),
				dejoiy_library_catalog_entry( 'The Enchiridion', 'Epictetus', 45109, 'Stoic handbook for resilience and self-control.' ),
				dejoiy_library_catalog_entry( 'The Confessions of Saint Augustine', 'Saint Augustine', 205, 'Influential memoir of inner struggle and transformation.' ),
				dejoiy_library_catalog_entry( 'Essays of Michel de Montaigne — Volume 1', 'Michel de Montaigne', 3588, 'Renaissance essays on self-knowledge and living.' ),
				dejoiy_library_catalog_entry( 'The Prophet', 'Kahlil Gibran', 15238, 'Poetic reflections on life, love, and purpose.' ),
				dejoiy_library_catalog_entry( 'Self-Reliance', 'Ralph Waldo Emerson', 16643, 'Defining essay on independence and authentic living.' ),
				dejoiy_library_catalog_entry( 'The Master Key System', 'Charles F. Haanel', 14328, 'Early 20th-century program on mental focus and success.' ),
			),
		),
		'design'            => array(
			'label' => 'Design',
			'books' => array(
				dejoiy_library_catalog_entry( 'The Grammar of Ornament', 'Owen Jones', 13055, 'Historic visual reference of pattern and ornament.' ),
				dejoiy_library_catalog_entry( 'The Decoration of Houses', 'Edith Wharton & Ogden Codman', 31967, 'Classic interior design and spatial harmony.' ),
				dejoiy_library_catalog_entry( 'Arts and Crafts Essays', 'William Morris et al.', 20666, 'Foundational essays on craft, beauty, and design.' ),
				dejoiy_library_catalog_entry( 'The Practice and Science of Drawing', 'Harold Speed', 16955, 'Fundamentals of line, form, and visual composition.' ),
				dejoiy_library_catalog_entry( 'A Treatise on Painting', 'Leonardo da Vinci', 5605, 'Renaissance notes on art, light, and composition.' ),
				dejoiy_library_catalog_entry( 'The Book of Art for Young People', 'Agnes Ethel Conway', 20239, 'Accessible introduction to art history and appreciation.' ),
				dejoiy_library_catalog_entry( 'Thought-Forms', 'Annie Besant & C. W. Leadbeater', 53440, 'Visual study of color, form, and perception.' ),
				dejoiy_library_catalog_entry( 'The Stones of Venice — Volume I', 'John Ruskin', 30804, 'Ruskin on architecture, craft, and Gothic beauty.' ),
				dejoiy_library_catalog_entry( 'Alphabets Old and New', 'Lewis F. Day', 43077, 'Historical letterforms and typographic design.' ),
				dejoiy_library_catalog_entry( 'Colour', 'A. H. Church', 24699, 'Science and aesthetics of color for artists.' ),
			),
		),
		'marketing'         => array(
			'label' => 'Marketing',
			'books' => array(
				dejoiy_library_catalog_entry( 'Scientific Advertising', 'Claude C. Hopkins', 45159, 'Foundational text on measurable advertising.' ),
				dejoiy_library_catalog_entry( 'My Life in Advertising', 'Claude C. Hopkins', 13298, 'Memoir and case studies from a master copywriter.' ),
				dejoiy_library_catalog_entry( 'The Art of Worldly Wisdom', 'Baltasar Gracián', 1685, 'Maxims on persuasion, reputation, and influence.' ),
				dejoiy_library_catalog_entry( 'Phineas Finn', 'Anthony Trollope', 1860, 'Victorian novel of politics, society, and ambition.' ),
				dejoiy_library_catalog_entry( 'The Science of Getting Rich', 'Wallace D. Wattles', 32591, 'Classic on value creation and purposeful action.' ),
				dejoiy_library_catalog_entry( 'The Art of Money Getting', 'P. T. Barnum', 514, 'Showmanship, promotion, and public attention.' ),
				dejoiy_library_catalog_entry( 'The American Credo', 'George Jean Nathan & H. L. Mencken', 1273, 'Satirical look at American myths and messaging.' ),
				dejoiy_library_catalog_entry( 'Extraordinary Popular Delusions', 'Charles Mackay', 24518, 'Mass enthusiasm, bubbles, and persuasion.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Andrew Carnegie', 'Andrew Carnegie', 17976, 'Brand, reputation, and industrial marketing power.' ),
				dejoiy_library_catalog_entry( 'On Liberty', 'John Stuart Mill', 34901, 'Argument for open markets of ideas and speech.' ),
			),
		),
		'ai-technology'     => array(
			'label' => 'AI & Technology',
			'books' => array(
				dejoiy_library_catalog_entry( 'Frankenstein', 'Mary Shelley', 84, 'Foundational science-fiction on creation and responsibility.' ),
				dejoiy_library_catalog_entry( 'The Time Machine', 'H. G. Wells', 35, 'Classic future technology and social speculation.' ),
				dejoiy_library_catalog_entry( 'The War of the Worlds', 'H. G. Wells', 36, 'Alien invasion and technological shock.' ),
				dejoiy_library_catalog_entry( 'Twenty Thousand Leagues Under the Sea', 'Jules Verne', 164, 'Adventure of submarine engineering and exploration.' ),
				dejoiy_library_catalog_entry( 'The Invisible Man', 'H. G. Wells', 5230, 'Science, ethics, and unintended consequences.' ),
				dejoiy_library_catalog_entry( 'Flatland', 'Edwin A. Abbott', 201, 'Mathematical allegory of dimensions and perception.' ),
				dejoiy_library_catalog_entry( 'Erewhon', 'Samuel Butler', 1906, 'Satire of machines, progress, and society.' ),
				dejoiy_library_catalog_entry( 'The Machine Stops', 'E. M. Forster', 3173, 'Prescient story of networked isolation and technology.' ),
				dejoiy_library_catalog_entry( 'R.U.R.', 'Karel Čapek', 59112, 'Play that introduced the word “robot”.' ),
				dejoiy_library_catalog_entry( 'A Journey to the Centre of the Earth', 'Jules Verne', 18857, 'Exploration, science, and technological daring.' ),
			),
		),
		'creativity'        => array(
			'label' => 'Creativity',
			'books' => array(
				dejoiy_library_catalog_entry( "Alice's Adventures in Wonderland", 'Lewis Carroll', 11, 'Imaginative classic of play, logic, and wonder.' ),
				dejoiy_library_catalog_entry( 'The Picture of Dorian Gray', 'Oscar Wilde', 174, 'Novel on art, beauty, and moral consequence.' ),
				dejoiy_library_catalog_entry( 'The Republic', 'Plato', 1497, 'Dialogue on justice, art, and the ideal society.' ),
				dejoiy_library_catalog_entry( 'Paradise Lost', 'John Milton', 26, 'Epic poem on creation, rebellion, and imagination.' ),
				dejoiy_library_catalog_entry( 'Don Quixote', 'Miguel de Cervantes', 996, 'Masterpiece on imagination, idealism, and story.' ),
				dejoiy_library_catalog_entry( 'Leaves of Grass', 'Walt Whitman', 1322, 'American verse celebrating self and creative life.' ),
				dejoiy_library_catalog_entry( 'Great Expectations', 'Charles Dickens', 1400, 'Coming-of-age novel of ambition and transformation.' ),
				dejoiy_library_catalog_entry( 'Crime and Punishment', 'Fyodor Dostoyevsky', 2554, 'Psychological novel of guilt, choice, and redemption.' ),
				dejoiy_library_catalog_entry( 'The Decameron', 'Giovanni Boccaccio', 1837, 'Renaissance storytelling and creative narrative.' ),
				dejoiy_library_catalog_entry( "Grimm's Fairy Tales", 'Jacob & Wilhelm Grimm', 2591, 'Folk tales that shaped modern creative tradition.' ),
			),
		),
		'productivity'      => array(
			'label' => 'Productivity',
			'books' => array(
				dejoiy_library_catalog_entry( 'Walden', 'Henry David Thoreau', 205, 'Reflections on simple living and intentional time.' ),
				dejoiy_library_catalog_entry( 'How to Live on Twenty-Four Hours a Day', 'Arnold Bennett', 2274, 'Classic on attention, evenings, and daily energy.' ),
				dejoiy_library_catalog_entry( 'Self-Help', 'Samuel Smiles', 935, 'Victorian guide to diligence, skill, and improvement.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Andrew Carnegie', 'Andrew Carnegie', 17976, 'On discipline, learning, and building enterprises.' ),
				dejoiy_library_catalog_entry( "The Pilgrim's Progress", 'John Bunyan', 131, 'Allegory of perseverance toward a chosen goal.' ),
				dejoiy_library_catalog_entry( 'The Art of Money Getting', 'P. T. Barnum', 514, 'Habits of enterprise and practical effort.' ),
				dejoiy_library_catalog_entry( 'As a Man Thinketh', 'James Allen', 4507, 'Mental habits that shape daily effectiveness.' ),
				dejoiy_library_catalog_entry( 'On the Shortness of Life', 'Seneca', 4276, 'Stoic time management and priority.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Benjamin Franklin', 'Benjamin Franklin', 148, 'Franklin on habits, craft, and civic productivity.' ),
				dejoiy_library_catalog_entry( 'The Master Key System', 'Charles F. Haanel', 14328, 'Focus, planning, and systematic personal work.' ),
			),
		),
		'startups'          => array(
			'label' => 'Startups',
			'books' => array(
				dejoiy_library_catalog_entry( 'The Art of War', 'Sun Tzu', 132, 'Strategy for competition and scarce resources.' ),
				dejoiy_library_catalog_entry( 'The Prince', 'Niccolò Machiavelli', 1232, 'Negotiation, power, and organizational politics.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Benjamin Franklin', 'Benjamin Franklin', 148, 'Builder mindset, networks, and experimentation.' ),
				dejoiy_library_catalog_entry( 'The Autobiography of Andrew Carnegie', 'Andrew Carnegie', 17976, 'Scaling industry and capital in a new economy.' ),
				dejoiy_library_catalog_entry( 'Acres of Diamonds', 'Russell H. Conwell', 368, 'Finding opportunity close to home.' ),
				dejoiy_library_catalog_entry( 'Essays on Political Economy', 'Frédéric Bastiat', 3560, 'Incentives, trade, and policy for founders.' ),
				dejoiy_library_catalog_entry( 'Extraordinary Popular Delusions', 'Charles Mackay', 24518, 'Bubbles, hype, and market psychology.' ),
				dejoiy_library_catalog_entry( 'The Art of Money Getting', 'P. T. Barnum', 514, 'Promotion, hustle, and customer attention.' ),
				dejoiy_library_catalog_entry( 'Scientific Advertising', 'Claude C. Hopkins', 45159, 'Testing, measurement, and growth messaging.' ),
				dejoiy_library_catalog_entry( 'On Liberty', 'John Stuart Mill', 34901, 'Freedom to innovate and challenge ideas.' ),
			),
		),
		'future-innovation' => array(
			'label' => 'Future & Innovation',
			'books' => array(
				dejoiy_library_catalog_entry( 'Looking Backward', 'Edward Bellamy', 619, 'Utopian vision of a future industrial society.' ),
				dejoiy_library_catalog_entry( 'News from Nowhere', 'William Morris', 326, 'Future society built on craft and equality.' ),
				dejoiy_library_catalog_entry( 'When the Sleeper Wakes', 'H. G. Wells', 775, 'Dystopian future of capital and technology.' ),
				dejoiy_library_catalog_entry( 'The Time Machine', 'H. G. Wells', 35, 'Far-future evolution and social change.' ),
				dejoiy_library_catalog_entry( 'Frankenstein', 'Mary Shelley', 84, 'Ethics of innovation and created intelligence.' ),
				dejoiy_library_catalog_entry( 'The Machine Stops', 'E. M. Forster', 3173, 'Dependence on networked machine civilization.' ),
				dejoiy_library_catalog_entry( 'Flatland', 'Edwin A. Abbott', 201, 'Perspective, abstraction, and new dimensions.' ),
				dejoiy_library_catalog_entry( 'The War of the Worlds', 'H. G. Wells', 36, 'Technological disruption from an outside force.' ),
				dejoiy_library_catalog_entry( 'Erewhon', 'Samuel Butler', 1906, 'Satire of machine evolution and social taboo.' ),
				dejoiy_library_catalog_entry( 'R.U.R.', 'Karel Čapek', 59112, 'Automation, labor, and artificial workers.' ),
			),
		),
	);
}

/**
 * Flat list of all catalog entries with category slug.
 *
 * @return array<int, array{slug: string, title: string, author: string, gid: int, blurb: string}>
 */
function dejoiy_library_get_catalog_flat() {
	$flat = array();
	foreach ( dejoiy_library_get_catalog() as $slug => $group ) {
		foreach ( $group['books'] as $book ) {
			$entry         = dejoiy_library_normalize_catalog_book( $book );
			$entry['slug'] = $slug;
			$flat[]        = $entry;
		}
	}
	return $flat;
}

/**
 * Unique Gutenberg IDs for sync (one WooCommerce product per ebook).
 *
 * @return array<int, array{slug: string, title: string, author: string, gid: int, blurb: string}>
 */
function dejoiy_library_get_catalog_sync_queue() {
	$queue = array();
	$seen  = array();
	foreach ( dejoiy_library_get_catalog_flat() as $entry ) {
		if ( empty( $entry['gid'] ) || isset( $seen[ $entry['gid'] ] ) ) {
			continue;
		}
		$seen[ $entry['gid'] ] = true;
		$queue[]               = $entry;
	}
	return $queue;
}

/**
 * @param array|string $book Catalog entry or legacy title string.
 * @return array{title: string, author: string, gid: int, blurb: string}
 */
function dejoiy_library_normalize_catalog_book( $book ) {
	if ( is_string( $book ) ) {
		return array(
			'title'  => $book,
			'author' => 'Unknown',
			'gid'    => 0,
			'blurb'  => '',
		);
	}
	if ( ! is_array( $book ) ) {
		return array(
			'title'  => '',
			'author' => '',
			'gid'    => 0,
			'blurb'  => '',
		);
	}
	return array(
		'title'  => isset( $book['title'] ) ? (string) $book['title'] : '',
		'author' => isset( $book['author'] ) ? (string) $book['author'] : '',
		'gid'    => isset( $book['gid'] ) ? (int) $book['gid'] : 0,
		'blurb'  => isset( $book['blurb'] ) ? (string) $book['blurb'] : '',
	);
}

/**
 * Featured collection cards on the landing page.
 *
 * @return array<int, array{slug: string, title: string, desc: string, icon: string}>
 */
function dejoiy_library_get_collections() {
	return array(
		array(
			'slug'  => 'startups',
			'title' => 'Build a Startup',
			'desc'  => 'Strategy, capital, and founder classics (public domain).',
			'icon'  => '🚀',
		),
		array(
			'slug'  => 'ai-technology',
			'title' => 'Learn AI',
			'desc'  => 'Sci-fi & futures that shaped how we think about machines.',
			'icon'  => '🤖',
		),
		array(
			'slug'  => 'design',
			'title' => 'Master Design',
			'desc'  => 'Ornament, craft, drawing, and visual thinking.',
			'icon'  => '✦',
		),
		array(
			'slug'  => 'psychology',
			'title' => 'Psychology Secrets',
			'desc'  => 'Mind, crowds, dreams, and human behavior.',
			'icon'  => '🧠',
		),
		array(
			'slug'  => 'business',
			'title' => 'Business Empire',
			'desc'  => 'Economics, leadership, and enterprise classics.',
			'icon'  => '👑',
		),
		array(
			'slug'  => 'productivity',
			'title' => 'Productivity Mastery',
			'desc'  => 'Time, habits, and deliberate living.',
			'icon'  => '⚡',
		),
		array(
			'slug'  => 'creativity',
			'title' => 'Creator Economy',
			'desc'  => 'Literature and art that train creative courage.',
			'icon'  => '🎨',
		),
		array(
			'slug'  => 'future-innovation',
			'title' => 'Future Technology',
			'desc'  => 'Utopias, dystopias, and visions of tomorrow.',
			'icon'  => '🔮',
		),
	);
}

/**
 * Galaxy orbit topic labels.
 *
 * @return string[]
 */
function dejoiy_library_galaxy_topics() {
	return array(
		'Business',
		'AI',
		'Psychology',
		'Design',
		'Marketing',
		'Creativity',
		'Startups',
		'Productivity',
	);
}

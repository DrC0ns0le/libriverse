-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Generation Time: Nov 13, 2024 at 01:26 AM
-- Server version: 11.5.2-MariaDB-ubu2404
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `libriverse`
--

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `title`, `description`, `image_link`) VALUES
(1, 'LibriVerse\'s 16 Book Picks for Fall 2024', 'As the days get shorter and the sweaters come out, it’s the perfect excuse to grab a book and cozy up! We’ve put together a list of 16 books you won’t want to miss this fall, with everything from page-turning mysteries to inspiring true stories. Whether you’re looking for a weekend escape or something to get lost in on a rainy afternoon, these reads are packed with twists, laughs, and plenty of surprises. So, get comfy, and let\'s dive into some seriously great books for Fall 2024!', 'assets/banners/banner1.jpg'),
(2, 'Readers\' 15 Most Anticipated November Books', 'Hey there, book lovers! November is here, and that means it\'s time to cozy up with some amazing new reads. We’re super excited to share our list of 15 Most Anticipated Books for November, so browse through our picks and mark your calendars! Happy reading!', 'assets/banners/banner2.png'),
(3, 'All-Time Classics: Books That Everyone Should Read At Least Once', 'Explore our All-Time Classics collection, a curated list of books that everyone should experience at least once! These literary gems span genres and eras, each offering insights and stories that resonate across time and cultures. Whether you\'re a lifelong reader or just starting your literary journey, these essential reads promise to enrich your perspective and ignite your imagination. Grab a book, settle in, and let these stories spark your curiosity and conversation for years to come!', 'assets/banners/banner3.png'),
(4, 'Exclusive Giveaway: Bookworm Edition!', 'Are you up for a challenge? Join us in our book giveaway where you could win some of the most beloved titles in literature! Push yourself to read a selection of amazing books over the next month. Whether you prefer heartwarming tales, thrilling mysteries, or thought-provoking non-fiction, there’s something for every book lover! Share your progress with us on social media using our challenge hashtag, and connect with fellow readers who are equally passionate about books. Let’s make reading fun and rewarding—who\'s in?', 'assets/banners/banner4.png');

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `catalog_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `bookmark`
--

INSERT INTO `bookmark` (`id`, `user_id`, `catalog_id`) VALUES
(3, 6, 9),
(10, 6, 20),
(22, 6, 29),
(24, 6, 16),
(25, 6, 18),
(31, 6, 83),
(33, 8, 25),
(34, 8, 85),
(35, 8, 1),
(36, 8, 5),
(39, 8, 82),
(40, 8, 21),
(42, 8, 9),
(43, 8, 56),
(44, 8, 67),
(45, 8, 39);

-- --------------------------------------------------------

--
-- Table structure for table `catalog`
--

CREATE TABLE `catalog` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `ratings` decimal(3,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('physical','electronic') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `inventory` int(11) NOT NULL,
  `pdf_link` varchar(255) DEFAULT NULL,
  `image_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `catalog`
--

INSERT INTO `catalog` (`id`, `title`, `author`, `publisher`, `genre`, `language`, `ratings`, `description`, `type`, `price`, `inventory`, `pdf_link`, `image_link`) VALUES
(1, 'It Ends with Us', 'Colleen Hoover', 'Atria Books', 'Romance', 'English', 4.14, 'It Ends with Us is a book that follows a girl named Lily who has just moved and is ready to start her life after college. Lily then meets a guy named Ryle and she falls for him. As she is developing feelings for Ryle, Atlas, her first love, reappears and challenges the relationship between Lily and Ryle.', 'physical', 19.99, 10, NULL, 'assets/thumbnails/it_ends_with_us.jpg'),
(2, 'I Want to Die But I Want to Eat Tteokpokki', 'Baek Se-hee', 'Bloomsbury', 'Memoir', 'English', 3.27, 'At once personal and universal, this book is about finding a path to awareness, understanding, and wisdom. A South Korean author recounts her long journey through anxiety and depression. Tteokbokki is a popular Korean dish of bland rice cakes immersed in a spicy pepper sauce.', 'physical', 23.99, 5, NULL, 'assets/thumbnails/i_want_to_die_but_i_want_to_eat_tteokbokki.jpg'),
(3, 'A Promised Land', 'Barack Obama', 'Crown', 'Biography', 'English', 4.33, 'A memoir that chronicles Barack Obama\'s journey from a community organizer to becoming the 44th President of the United States.', 'physical', 45.99, 53, NULL, 'assets/thumbnails/a_promised_land.jpg'),
(4, 'Odyssey', 'Stephen Fry', 'Penguin', 'Mythology', 'English', 4.41, 'Follow Odysseus after he leaves the fallen city of Troy and takes ten long dramatic years - battling monsters, the temptations of goddesses and suffering the curse of Poseidon - to voyage home to his wife Penelope on the island of Ithaca.', 'physical', 37.99, 60, NULL, 'assets/thumbnails/odyssey.jpg'),
(5, 'Nexus: A Brief History of Information Networks from the Stone Age to AI', 'Yuval Noah Harari', 'Random House', 'Non-fiction', 'English', 4.17, 'Nexus looks through the long lens of human history to consider how the flow of information has shaped us, and our world.', 'physical', 21.99, 82, NULL, 'assets/thumbnails/nexus.jpg'),
(6, 'Blue Sisters', 'Coco Mellors', 'Ballantine Books', 'Contemporary', 'English', 4.10, 'Three estranged siblings return to their family home in New York after their beloved sister\'s death in this unforgettable story of grief, identity, and the complexities of family.', 'physical', 27.99, 28, NULL, 'assets/thumbnails/blue_sisters.jpg'),
(7, 'Bringer of Dust', 'J.M. Miro', 'Flatiron Books', 'Fantasy', 'English', 4.19, 'A malevolent figure, known only as the Abbess, desires the dust for her own ends. And deep in the world of the dead, a terrible evil stirs - an evil which the corrupted dust just might hold the secret to reviving, or destroying forever.', 'physical', 25.99, 19, NULL, 'assets/thumbnails/bringer_of_dust.jpg'),
(8, 'The Mesmerist', 'Caroline Woods', 'Doubleday', 'Mystery', 'English', 3.75, 'This historical mystery takes a look at the early twentieth century fascination with spiritualism and mesmerism utilizing the viewpoints of strong unique women. Set around Bethany House, a home for unwed mothers in 1894 Minneapolis the meandering plot reveals the stories of the occupants and how they came to be there.', 'physical', 17.99, 7, NULL, 'assets/thumbnails/the_mesmerist.jpg'),
(9, 'Educated', 'Tara Westover', 'Random House', 'Biography', 'English', 4.47, 'Educated is an account of the struggle for self-invention. It is a tale of fierce family loyalty and of the grief that comes with severing the closest of ties. With the acute insight that distinguishes all great writers, Westover has crafted a universal coming-of-age story that gets to the heart of what an education is and what it offers: the perspective to see one\'s life through new eyes and the will to change it.', 'physical', 39.99, 40, NULL, 'assets/thumbnails/educated.jpg'),
(10, 'Outliers: The Story of Success', 'Malcolm Gladwell', 'Little, Brown and Company', 'Self-help', 'English', 4.19, 'Outliers is a collection of stories, each exploring a variety of external factors that contribute to success. Malcolm Gladwell argues that extraordinarily successful people—or outliers—reached that point not just because of hard work and determination, but also thanks to luck, timing, and opportunities.', 'physical', 23.99, 53, NULL, 'assets/thumbnails/outliers.jpg'),
(11, 'They Called Us Enemy', 'George Takei', 'Top Shelf Productions', 'War', 'English', 4.42, 'A graphic memoir recounting actor/author/activist George Takei\'s childhood imprisoned within American concentration camps during World War II. Experience the forces that shaped an American icon -- and America itself.', 'physical', 39.99, 15, NULL, 'assets/thumbnails/they_called_us_enemy.jpg'),
(12, 'Gone with the Wind', 'Margaret Mitchell', 'Warner Books', 'Classics', 'English', 4.31, 'Scarlett O\'Hara, the beautiful, spoiled daughter of a well-to-do Georgia plantation owner, must use every means at her disposal to claw her way out of the poverty she finds herself in after Sherman\'s March to the Sea.', 'physical', 15.99, 30, NULL, 'assets/thumbnails/gone_with_the_wind.jpg'),
(13, 'Sense and Sensibility', 'Jane Austen', 'Penguin Books', 'Classics', 'English', 4.09, 'The novel follows the three Dashwood sisters and their widowed mother as they are forced to leave the family estate at Norland Park and move to Barton Cottage, a modest home on the property of distant relative Sir John Middleton. There Elinor and Marianne experience love, romance, and heartbreak.', 'physical', 15.99, 19, NULL, 'assets/thumbnails/sense_and_sensibility.jpg'),
(14, 'The Thorn Birds', 'Colleen McCullough', 'Avon Books', 'Romance', 'English', 4.25, 'The Thorn Birds traces the lives of the members of the Cleary family over the course of three generations, from their poor existence in New Zealand to their eventual move to Australia when a distant relative summons them and promises them a more stable life.', 'physical', 19.99, 9, NULL, 'assets/thumbnails/the_thorn_birds.jpg'),
(15, 'And Then There Were None', 'Agatha Christie', 'St. Martin\'s Griffin', 'Mystery', 'English', 4.28, 'Ten strangers arrive on an island invited by an unknown host. Each of them has a secret to hide and a crime for which they must pay.', 'physical', 23.99, 25, NULL, 'assets/thumbnails/and_then_there_were_none.jpg'),
(16, 'The Green Mile', 'Stephen King', 'Penguin Signet', 'Crime', 'English', 4.48, 'They call death row at Cold Mountain Penitentiary \'The Green Mile.\' John Coffey, sentenced to die for the rape and murder of two young girls, is the latest addition to the Mile. Paul Edgecomb, the ward superintendent, discovers that there is more to John Coffey than meets the eye, for this friendly giant has the power to heal. ', 'physical', 25.99, 1, NULL, 'assets/thumbnails/the_green_mile.jpg'),
(17, 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 'Contemporary', 'English', 4.35, 'Twelve-year-old Amir is desperate to win the local kite-fighting tournament and his loyal friend Hassan promises to help him. But neither of the boys can foresee what would happen to Hassan that afternoon, an event that is to shatter their lives. After the Russians invade and the family is forced to flee to America, Amir realises that one day he must return to an Afghanistan under Taliban rule to find the one thing that his new world cannot grant him: redemption.', 'physical', 35.99, 67, NULL, 'assets/thumbnails/the_kite_runner.jpg'),
(18, 'Johnny Got His Gun', 'Dalton Trumbo', 'Citadel', 'War', 'English', 4.20, 'This is no ordinary novel. This is a novel that never takes the easy way out: it is shocking, violent, terrifying, horrible, uncompromising, brutal, remorseless and gruesome... but so is war.', 'physical', 25.99, 27, NULL, 'assets/thumbnails/johnny_got_his_gun.jpg'),
(19, 'Think Twice', 'Harlan Coben', 'Grand Central Publishing', 'Mystery', 'English', 4.31, 'A man presumed dead is suddenly wanted for murder in this thriller of secrets, lies, and dangerous conspiracies that threaten to cover up the truth.', 'physical', 23.99, 30, NULL, 'assets/thumbnails/think_twice.jpg'),
(20, 'A Calamity of Souls', 'David Baldacci', 'Grand Central Publishing', 'Mystery', 'English', 4.52, 'Set in the tumultuous year of 1968 in southern Virginia, a racially-charged murder case sets a duo of white and Black lawyers against a deeply unfair system.', 'physical', 19.99, 30, NULL, 'assets/thumbnails/a_calamity_of_souls.jpg'),
(21, 'The Boy in the Striped Pajamas', 'John Boyne', 'David Fickling Books', 'War', 'English', 4.16, 'A historical fiction novel by Irish novelist John Boyne. The plot concerns a German boy named Bruno whose father is the commandant of Auschwitz and Bruno\'s friendship with a Jewish detainee named Shmuel.', 'physical', 19.99, 28, NULL, 'assets/thumbnails/the_boy_in_the_striped_pajamas.jpg'),
(22, 'Gerald\'s Game', 'Stephen King', 'Smithmark Publishers', 'Horror', 'English', 3.58, 'It tells the story of Jessie Burlingame, who finds herself handcuffed to a bed in an isolated lake house after her husband dies unexpectedly.', 'physical', 19.99, 4, NULL, 'assets/thumbnails/geralds_game.jpg'),
(23, 'The Picture of Dorian Gray', 'Oscar Wilde', 'Random House', 'Classics', 'English', 4.13, 'Oscar Wilde’s only novel is the dreamlike story of a young man who sells his soul for eternal youth and beauty.', 'physical', 19.99, 65, NULL, 'assets/thumbnails/the_picture_of_dorian_gray.jpg'),
(24, 'Slaughterhouse-Five', 'Kurt Vonnegut Jr.', 'Dial Press', 'War', 'English', 4.10, 'Centering on the infamous World War II firebombing of Dresden, the novel is the result of what Kurt Vonnegut described as a twenty-three-year struggle to write a book about what he had witnessed as an American prisoner of war.', 'physical', 23.99, 45, NULL, 'assets/thumbnails/slaughterhouse_five.jpg'),
(25, 'The Color Purple', 'Alice Walker', 'Penguin Books', 'Historical', 'English', 4.26, 'A powerful novel that explores the struggles and resilience of African-American women in the early 20th century American South, focusing on themes of love, empowerment, and overcoming adversity.', 'physical', 23.99, 21, NULL, 'assets/thumbnails/the_color_purple.jpg'),
(26, 'Don Quixote', 'Miguel de Cervantes', 'Harper Collins', 'Classics', 'English', 4.02, 'The story of a chivalrous yet misguided knight, Don Quixote, who sets out on a quest to revive chivalry and serve his nation.', 'physical', 15.99, 40, NULL, 'assets/thumbnails/don_quixote.jpg'),
(27, 'The Old Man and the Sea', 'Ernest Hemingway', 'Scribner', 'Classics', 'English', 3.78, 'A classic tale of an epic struggle between an old fisherman and a giant marlin, exploring themes of resilience and perseverance.', 'physical', 12.49, 35, NULL, 'assets/thumbnails/the_old_man_and_the_sea.jpg'),
(28, 'The Poisonwood Bible', 'Barbara Kingsolver', 'Harper Perennial', 'Historical', 'English', 4.06, 'The story of a missionary family in the Congo, exploring themes of family, faith, and culture clash during turbulent times.', 'physical', 18.75, 28, NULL, 'assets/thumbnails/the_poisonwood_bible.jpg'),
(29, 'Life of Pi', 'Yann Martel', 'Mariner Books', 'Fantasy', 'English', 3.91, 'An incredible story of survival, as a young boy named Pi is stranded on a lifeboat in the Pacific Ocean with a Bengal tiger.', 'physical', 14.89, 50, NULL, 'assets/thumbnails/life_of_pi.jpg'),
(30, 'Great Expectations', 'Charles Dickens', 'Penguin Classics', 'Classics', 'English', 3.78, 'The tale of Pip, an orphan, who navigates a journey of personal growth and faces the stark reality of Victorian society.', 'physical', 9.99, 60, NULL, 'assets/thumbnails/great_expectations.jpg'),
(31, 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 'Classics', 'English', 4.26, 'A tale of love and social standing as Elizabeth Bennet navigates issues of marriage, morality, and misunderstandings in 19th century England.', 'physical', 10.99, 40, NULL, 'assets/thumbnails/pride_and_prejudice.jpg'),
(32, 'To Kill a Mockingbird', 'Harper Lee', 'J.B. Lippincott & Co.', 'Classics', 'English', 4.28, 'A profound novel that explores racial injustice in the American South through the eyes of young Scout Finch.', 'physical', 12.49, 35, NULL, 'assets/thumbnails/to_kill_a_mockingbird.jpg'),
(33, 'Moby Dick', 'Herman Melville', 'Harper & Brothers', 'Classics', 'English', 3.51, 'The epic saga of Captain Ahab\'s obsessive quest to kill the great white whale, Moby Dick.', 'physical', 11.89, 28, NULL, 'assets/thumbnails/moby_dick.jpg'),
(34, '1984', 'George Orwell', 'Secker & Warburg', 'Classics', 'English', 4.17, 'A dystopian novel that warns of the dangers of totalitarianism and extreme political ideology.', 'physical', 13.49, 45, NULL, 'assets/thumbnails/1984.jpg'),
(35, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 'Classics', 'English', 3.91, 'The tragic story of Jay Gatsby and his unrequited love for Daisy Buchanan, set in the Jazz Age.', 'physical', 9.99, 52, NULL, 'assets/thumbnails/the_great_gatsby.jpg'),
(36, 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown and Company', 'Classics', 'English', 3.80, 'The story of Holden Caulfield, a teenager disillusioned with the phoniness of society and struggling with his identity.', 'physical', 10.49, 37, NULL, 'assets/thumbnails/the_catcher_in_the_rye.jpg'),
(37, 'Brave New World', 'Aldous Huxley', 'Chatto & Windus', 'Classics', 'English', 3.99, 'A vision of a dystopian future where individuality is suppressed for the sake of societal stability.', 'physical', 11.75, 48, NULL, 'assets/thumbnails/brave_new_world.jpg'),
(38, 'War and Peace', 'Leo Tolstoy', 'The Russian Messenger', 'Classics', 'English', 4.11, 'An epic novel set against the backdrop of the Napoleonic wars, exploring themes of love, fate, and the human spirit.', 'physical', 14.89, 25, NULL, 'assets/thumbnails/war_and_peace.jpg'),
(39, 'Crime and Punishment', 'Fyodor Dostoevsky', 'The Russian Messenger', 'Classics', 'English', 4.21, 'The psychological journey of a young man who commits a crime and is consumed by guilt and paranoia.', 'physical', 13.25, 34, NULL, 'assets/thumbnails/crime_and_punishment.jpg'),
(40, 'Jane Eyre', 'Charlotte Brontë', 'Smith, Elder & Co.', 'Classics', 'English', 4.12, 'The story of an orphaned girl who becomes a governess and falls in love with her mysterious employer, Mr. Rochester.', 'physical', 12.99, 43, NULL, 'assets/thumbnails/jane_eyre.jpg'),
(41, 'Atomic Habits', 'James Clear', 'Avery', 'Self-help', 'English', 4.37, 'A guide to building good habits and breaking bad ones with small changes that compound over time.', 'physical', 16.99, 45, NULL, 'assets/thumbnails/atomic_habits.jpg'),
(42, 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 'Self-help', 'English', 4.10, 'A timeless guide to personal and professional effectiveness, focusing on character and productivity.', 'physical', 14.75, 50, NULL, 'assets/thumbnails/the_7_habits_of_highly_effective_people.jpg'),
(43, 'How to Win Friends and Influence People', 'Dale Carnegie', 'Simon & Schuster', 'Self-help', 'English', 4.21, 'A classic guide to interpersonal skills and effective communication.', 'physical', 10.99, 40, NULL, 'assets/thumbnails/how_to_win_friends_and_influence_people.jpg'),
(44, 'You Are a Badass', 'Jen Sincero', 'Running Press', 'Self-help', 'English', 3.94, 'An inspiring guide to embracing self-confidence, changing beliefs, and creating a life you love.', 'physical', 12.99, 35, NULL, 'assets/thumbnails/you_are_a_badass.jpg'),
(45, 'The Power of Now', 'Eckhart Tolle', 'New World Library', 'Self-help', 'English', 4.13, 'A spiritual guide to living fully in the present moment.', 'physical', 15.49, 48, NULL, 'assets/thumbnails/the_power_of_now.jpg'),
(46, 'Think and Grow Rich', 'Napoleon Hill', 'The Ralston Society', 'Self-help', 'English', 4.17, 'A timeless classic on achieving financial success through mindset and goal-setting.', 'physical', 9.99, 52, NULL, 'assets/thumbnails/think_and_grow_rich.jpg'),
(47, 'Daring Greatly', 'Brené Brown', 'Avery', 'Self-help', 'English', 4.28, 'A powerful exploration of vulnerability, courage, and the importance of emotional resilience.', 'physical', 13.89, 42, NULL, 'assets/thumbnails/daring_greatly.jpg'),
(48, 'Mindset: The New Psychology of Success', 'Carol S. Dweck', 'Ballantine Books', 'Self-help', 'English', 4.06, 'A look at how our mindset influences our achievements and personal growth.', 'physical', 11.50, 39, NULL, 'assets/thumbnails/mindset_the_new_psychology_of_success.jpg'),
(49, 'The Four Agreements', 'Don Miguel Ruiz', 'Amber-Allen Publishing', 'Self-help', 'English', 4.20, 'A spiritual guide offering four principles to practice for inner peace and freedom.', 'physical', 10.25, 46, NULL, 'assets/thumbnails/the_four_agreements.jpg'),
(50, 'Awaken the Giant Within', 'Tony Robbins', 'Free Press', 'Self-help', 'English', 4.08, 'A guide to personal empowerment and achieving your life\'s goals through mental mastery.', 'physical', 17.00, 41, NULL, 'assets/thumbnails/awaken_the_giant_within.jpg'),
(51, 'The Diary of a Young Girl', 'Anne Frank', 'Contact Publishing', 'Biography', 'English', 4.13, 'The personal diary of Anne Frank, a young Jewish girl who hid from the Nazis during World War II.', 'physical', 10.99, 40, NULL, 'assets/thumbnails/diary_of_a_young_girl.jpg'),
(52, 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 'Biography', 'English', 4.16, 'A comprehensive biography of Steve Jobs, the co-founder of Apple Inc., detailing his life and career.', 'physical', 14.99, 30, NULL, 'assets/thumbnails/steve_jobs.jpg'),
(53, 'Long Walk to Freedom', 'Nelson Mandela', 'Little, Brown and Company', 'Biography', 'English', 4.35, 'The autobiography of Nelson Mandela, chronicling his journey from a rural village to becoming the president of South Africa.', 'physical', 12.50, 25, NULL, 'assets/thumbnails/long_walk_to_freedom.jpg'),
(55, 'Einstein: His Life and Universe', 'Walter Isaacson', 'Simon & Schuster', 'Biography', 'English', 4.10, 'A biography of Albert Einstein, exploring his theories, life, and impact on science and the world.', 'physical', 15.99, 35, NULL, 'assets/thumbnails/einstein.jpg'),
(56, 'In Cold Blood', 'Truman Capote', 'Random House', 'Crime', 'English', 4.05, 'A groundbreaking true-crime novel about the brutal murder of a family and the ensuing investigation.', 'physical', 13.99, 40, NULL, 'assets/thumbnails/in_cold_blood.jpg'),
(57, 'Gone Girl', 'Gillian Flynn', 'Crown Publishing', 'Crime', 'English', 4.06, 'A thriller about a marriage gone terribly wrong, with unexpected twists and turns.', 'physical', 28.49, 35, NULL, 'assets/thumbnails/gone_girl.jpg'),
(58, 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Norstedts Förlag', 'Crime', 'English', 4.15, 'A gripping mystery novel featuring a journalist and a hacker on the trail of a decades-old disappearance.', 'physical', 23.25, 50, NULL, 'assets/thumbnails/girl_with_dragon_tattoo.jpg'),
(59, 'Big Little Lies', 'Liane Moriarty', 'Penguin Publishing', 'Crime', 'English', 4.28, 'A story of secrets and lies unraveling in a wealthy community, ending in a shocking crime.', 'physical', 19.75, 45, NULL, 'assets/thumbnails/big_little_lies.jpg'),
(60, 'The Silence of the Lambs', 'Thomas Harris', 'St. Martin\'s Press', 'Crime', 'English', 4.22, 'A psychological thriller about an FBI trainee seeking the help of a manipulative serial killer to catch another.', 'physical', 25.99, 30, NULL, 'assets/thumbnails/silence_of_the_lambs.jpg'),
(61, 'The Sixth Extinction: An Unnatural History', 'Elizabeth Kolbert', 'Henry Holt and Company', 'Non-Fiction', 'English', 4.14, 'An exploration of mass extinctions throughout Earth\'s history, focusing on the current biodiversity crisis.', 'physical', 14.99, 40, NULL, 'assets/thumbnails/sixth_extinction.jpg'),
(62, 'Guns, Germs, and Steel: The Fates of Human Societies', 'Jared Diamond', 'W. W. Norton & Company', 'Non-Fiction', 'English', 4.03, 'An analysis of how environmental factors shaped the development and dominance of civilizations.', 'physical', 12.99, 30, NULL, 'assets/thumbnails/guns_germs_steel.jpg'),
(63, 'The Elegant Universe: Superstrings, Hidden Dimensions, and the Quest for the Ultimate Theory', 'Brian Greene', 'W. W. Norton & Company', 'Non-Fiction', 'English', 4.08, 'A look at string theory and its implications for our understanding of space, time, and the universe.', 'physical', 15.49, 25, NULL, 'assets/thumbnails/elegant_universe.jpg'),
(64, 'Freakonomics: A Rogue Economist Explores the Hidden Side of Everything', 'Steven D. Levitt and Stephen J. Dubner', 'HarperCollins', 'Non-Fiction', 'English', 4.00, 'An unconventional exploration of economics and the surprising reasons behind everyday phenomena.', 'physical', 10.99, 50, NULL, 'assets/thumbnails/freakonomics.jpg'),
(65, 'Cosmos', 'Carl Sagan', 'Random House', 'Non-Fiction', 'English', 4.36, 'A scientific classic that explores the universe, its origins, and humanity\'s place within it.', 'physical', 13.49, 35, NULL, 'assets/thumbnails/cosmos.jpg'),
(66, 'Mythos: The Greek Myths Retold', 'Stephen Fry', 'Penguin Books', 'Mythology', 'English', 4.34, 'A lively retelling of Greek myths with Stephen Fry’s wit and depth.', 'physical', 14.99, 40, NULL, 'assets/thumbnails/mythos.jpg'),
(67, 'Norse Mythology', 'Neil Gaiman', 'W. W. Norton & Company', 'Mythology', 'English', 4.22, 'Neil Gaiman’s unique retelling of Norse myths, from the origins of the nine worlds to Ragnarok.', 'physical', 15.99, 50, NULL, 'assets/thumbnails/norse_mythology.jpg'),
(68, 'Circe', 'Madeline Miller', 'Little, Brown and Company', 'Mythology', 'English', 4.26, 'A fresh take on the life of the witch Circe from Greek mythology, blending myth with modern storytelling.', 'physical', 13.99, 45, NULL, 'assets/thumbnails/circe.jpg'),
(69, 'The Shining', 'Stephen King', 'Doubleday', 'Horror', 'English', 4.23, 'A chilling story of a family isolated in a haunted hotel, where terrifying forces drive them toward madness.', 'physical', 15.99, 50, NULL, 'assets/thumbnails/the_shining.jpg'),
(70, 'Dracula', 'Bram Stoker', 'Archibald Constable and Company', 'Horror', 'English', 4.01, 'The classic tale of Count Dracula’s attempt to move from Transylvania to England, bringing terror with him.', 'physical', 9.49, 40, NULL, 'assets/thumbnails/dracula.jpg'),
(71, 'Bird Box', 'Josh Malerman', 'Ecco', 'Horror', 'English', 4.02, 'A suspenseful, post-apocalyptic thriller about a mother trying to protect her children in a world of unseen horrors.', 'physical', 12.75, 45, NULL, 'assets/thumbnails/bird_box.jpg'),
(72, 'Frankenstein', 'Mary Shelley', 'Lackington, Hughes, Harding, Mavor & Jones', 'Horror', 'English', 3.78, 'The story of Victor Frankenstein and the tragic creature he brings to life.', 'physical', 10.99, 30, NULL, 'assets/thumbnails/frankenstein.jpg'),
(73, 'Mexican Gothic', 'Silvia Moreno-Garcia', 'Del Rey', 'Horror', 'English', 3.93, 'A horror novel set in 1950s Mexico, where a young woman investigates sinister secrets in a mysterious mansion.', 'physical', 14.89, 40, NULL, 'assets/thumbnails/mexican_gothic.jpg'),
(74, 'The Haunting of Hill House', 'Shirley Jackson', 'Viking Press', 'Horror', 'English', 4.02, 'A psychological horror about four people who stay at a haunted mansion, each affected by its dark presence.', 'physical', 13.49, 38, NULL, 'assets/thumbnails/haunting_hill_house.jpg'),
(75, 'The Exorcist', 'William Peter Blatty', 'Harper & Row', 'Horror', 'English', 4.17, 'A terrifying tale of demonic possession and the battle to save a young girl’s soul.', 'physical', 11.99, 42, NULL, 'assets/thumbnails/the_exorcist.jpg'),
(76, 'Just Kids', 'Patti Smith', 'Ecco', 'Memoir', 'English', 4.22, 'A poetic and poignant story about Patti Smith’s friendship with photographer Robert Mapplethorpe in 1960s New York.', 'physical', 13.99, 35, NULL, 'assets/thumbnails/just_kids.jpg'),
(77, 'When Breath Becomes Air', 'Paul Kalanithi', 'Random House', 'Memoir', 'English', 4.36, 'A neurosurgeon’s profound reflections on facing terminal illness and grappling with life and death.', 'physical', 14.49, 40, NULL, 'assets/thumbnails/when_breath_becomes_air.jpg'),
(78, 'Wild: From Lost to Found on the Pacific Crest Trail', 'Cheryl Strayed', 'Alfred A. Knopf', 'Memoir', 'English', 4.04, 'An inspiring account of one woman’s solo hike along the Pacific Crest Trail as a journey of self-discovery and healing.', 'physical', 12.75, 45, NULL, 'assets/thumbnails/wild.jpg'),
(79, 'The Glass Castle', 'Jeannette Walls', 'Scribner', 'Memoir', 'English', 4.28, 'A moving story of resilience and family as Walls recalls her unconventional, troubled childhood.', 'physical', 10.99, 50, NULL, 'assets/thumbnails/glass_castle.jpg'),
(80, 'H Is for Hawk', 'Helen Macdonald', 'Grove Press', 'Memoir', 'English', 3.91, 'A reflective memoir about the author’s experience training a goshawk after her father’s passing, exploring grief and nature.', 'physical', 13.75, 38, NULL, 'assets/thumbnails/h_is_for_hawk.jpg'),
(81, 'The Name of the Wind', 'Patrick Rothfuss', 'DAW Books', 'Fantasy', 'English', 4.52, 'A compelling tale of a young man’s journey to become a powerful and legendary figure in a mystical world.', 'physical', 15.99, 42, NULL, 'assets/thumbnails/name_of_the_wind.jpg'),
(82, 'Mistborn: The Final Empire', 'Brandon Sanderson', 'Tor Books', 'Fantasy', 'English', 4.46, 'In a world ruled by a dark lord, a young street urchin discovers her powers and joins a rebellion to change her fate.', 'physical', 14.75, 37, NULL, 'assets/thumbnails/mistborn.jpg'),
(83, 'The Way of Kings', 'Brandon Sanderson', 'Tor Books', 'Fantasy', 'English', 4.65, 'An epic fantasy novel that follows the paths of multiple characters in a world scarred by a brutal war and violent storms.', 'physical', 18.99, 30, NULL, 'assets/thumbnails/way_of_kings.jpg'),
(84, 'A Game of Thrones', 'George R.R. Martin', 'Bantam Books', 'Fantasy', 'English', 4.44, 'The first book in the epic saga of power, betrayal, and the fight for the Iron Throne in the Seven Kingdoms.', 'physical', 16.50, 45, NULL, 'assets/thumbnails/game_of_thrones.jpg'),
(85, 'Good Omens', 'Neil Gaiman & Terry Pratchett', 'William Morrow', 'Fantasy', 'English', 4.25, 'A comedic tale about an angel and a demon working together to stop the apocalypse.', 'physical', 12.99, 33, NULL, 'assets/thumbnails/good_omens.jpg'),
(86, 'American Gods', 'Neil Gaiman', 'William Morrow', 'Fantasy', 'English', 4.11, 'A man named Shadow is thrust into a world of myth and mystery, encountering gods old and new in a battle for relevance.', 'physical', 13.49, 40, NULL, 'assets/thumbnails/american_gods.jpg'),
(87, 'The Lies of Locke Lamora', 'Scott Lynch', 'Gollancz', 'Fantasy', 'English', 4.29, 'A witty and action-packed fantasy heist story following the adventures of a master thief in the city of Camorr.', 'physical', 14.99, 36, NULL, 'assets/thumbnails/locke_lamora.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('Debit','Credit','Paypal','Grabpay') NOT NULL,
  `last_4_digits` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `user_id`, `type`, `last_4_digits`) VALUES
(1, 1, 'Credit', '1234'),
(2, 2, 'Debit', '5678'),
(3, 3, 'Paypal', '4321'),
(4, 4, 'Grabpay', '8765'),
(5, 5, 'Credit', '1111'),
(6, 6, 'Debit', '1234'),
(8, 6, 'Debit', '9088'),
(9, 6, 'Credit', '9080'),
(10, 6, 'Paypal', '2345'),
(11, 8, 'Debit', '1234'),
(12, 8, 'Grabpay', '5678');

-- --------------------------------------------------------

--
-- Table structure for table `pickup_location`
--

CREATE TABLE `pickup_location` (
  `id` int(11) NOT NULL,
  `library_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `opening_hours` text NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `pickup_location`
--

INSERT INTO `pickup_location` (`id`, `library_name`, `address`, `opening_hours`, `phone_number`) VALUES
(1, 'National Library', '100 Victoria St, Singapore 188064', 'Mon-Sun: 10:00 AM - 9:00 PM', '6332 3255'),
(2, 'Jurong Regional Library', '21 Jurong East Central 1, Singapore 609732', 'Mon-Sun: 10:00 AM - 9:00 PM', '6102 2532'),
(3, 'Woodlands Regional Library', '900 South Woodlands Dr, #01-03, Woodlands Civic Centre, Singapore 730900', 'Mon-Sun: 10:00 AM - 9:00 PM', '6212 5091'),
(4, 'Tampines Regional Library', '1 Tampines Walk, #02-01, Our Tampines Hub, Singapore 528523', 'Mon-Sun: 10:00 AM - 9:00 PM', '6129 3321'),
(5, 'Ang Mo Kio Public Library', '4300 Ang Mo Kio Ave 6, Singapore 569842', 'Mon-Sun: 10:00 AM - 9:00 PM', '6209 9174'),
(6, 'Bedok Public Library', '11 Bedok North St 1, Singapore 469662', 'Mon-Sun: 10:00 AM - 9:00 PM', '6243 3911'),
(7, 'Bukit Panjang Public Library', '1 Jelebu Road, #02-01, Bukit Panjang Plaza, Singapore 677743', 'Mon-Sun: 10:00 AM - 9:00 PM', '6769 1192'),
(8, 'Changi Regional Library', 'Changi City Point, 5 Changi Business Park Central 1, Singapore 486038', 'Mon-Sun: 10:00 AM - 9:00 PM', '6634 9535'),
(9, 'Marine Parade Public Library', '278 Marine Parade Rd, Singapore 449282', 'Mon-Sun: 10:00 AM - 9:00 PM', '6345 3200'),
(10, 'Sengkang Public Library', '1 Sengkang Square, #02-01, Compass One, Singapore 545078', 'Mon-Sun: 10:00 AM - 9:00 PM', '6489 3524');

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL,
  `status` enum('Requested','Preparing','Cancelled','Ready','Collected','Returned') NOT NULL,
  `status_last_updated` datetime NOT NULL,
  `rental_duration` int(11) NOT NULL,
  `pickup_location_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`id`, `user_id`, `catalog_id`, `status`, `status_last_updated`, `rental_duration`, `pickup_location_id`, `payment_id`) VALUES
(1, 6, 11, 'Returned', '2024-11-05 09:33:27', 4, 1, NULL),
(2, 6, 9, 'Preparing', '2024-11-05 09:31:35', 1, NULL, NULL),
(4, 6, 20, 'Requested', '2024-11-05 08:33:50', 6, 3, NULL),
(5, 6, 16, 'Collected', '2024-11-05 11:53:00', 49, 5, NULL),
(6, 6, 32, 'Ready', '2024-11-05 11:54:44', 25, 5, NULL),
(7, 8, 24, 'Requested', '2024-11-06 06:33:45', 2, 1, NULL),
(8, 8, 17, 'Requested', '2024-11-06 06:34:06', 5, NULL, NULL),
(9, 8, 7, 'Cancelled', '2024-11-06 06:34:30', 7, NULL, NULL),
(10, 6, 29, 'Cancelled', '2024-11-11 18:39:15', 9, NULL, NULL),
(11, 8, 81, 'Requested', '2024-11-12 08:29:01', 2, 4, NULL),
(12, 8, 20, 'Cancelled', '2024-11-12 08:29:36', 1, 4, NULL),
(13, 8, 65, 'Cancelled', '2024-11-12 08:31:05', 2, 3, NULL),
(14, 8, 4, 'Requested', '2024-11-12 12:17:16', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `catalog_id` bigint(20) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id`, `user_id`, `catalog_id`, `rating`, `review`) VALUES
(16, 1, 1, 5, 'A deeply emotional and touching story, one of my favorites!'),
(17, 1, 2, 4, 'A powerful narrative, but a little too dark for my taste.'),
(18, 1, 3, 5, 'An eye-opening read on political leadership and change.'),
(19, 1, 4, 3, 'Not my favorite, the plot felt a bit slow at times.'),
(20, 1, 5, 4, 'A great exploration of how networks shape everything around us.'),
(21, 1, 6, 5, 'I loved this book! Such an inspiring and heartfelt story.'),
(22, 1, 7, 4, 'A unique and interesting read, though the pacing could be better.'),
(23, 1, 8, 3, 'Not the best book I’ve read, but still interesting in parts.'),
(24, 1, 9, 5, 'Such an inspiring memoir that highlights the importance of education.'),
(25, 1, 10, 4, 'A fascinating take on success, though some parts were repetitive.'),
(26, 1, 11, 4, 'A compelling and thoughtful story, though it dragged a little in the middle.'),
(27, 1, 12, 5, 'A timeless classic, beautifully written and heart-wrenching.'),
(28, 1, 13, 4, 'A great mix of romance and reason, but a bit slow-paced at times.'),
(29, 1, 14, 5, 'One of the best books I’ve ever read! Deeply moving and emotional.'),
(30, 1, 15, 3, 'A decent read, but the ending didn’t quite satisfy me.'),
(31, 2, 1, 4, 'A great story, though I felt it could’ve had a faster pace at times.'),
(32, 2, 2, 5, 'Such a deep and powerful read that stays with you long after finishing.'),
(33, 2, 3, 4, 'An insightful and inspiring political memoir, but a bit lengthy.'),
(34, 2, 4, 3, 'An interesting concept, but it didn’t quite captivate me.'),
(35, 2, 5, 5, 'An incredible book about the importance of networks in modern life.'),
(36, 2, 6, 4, 'A beautiful story of family, though the ending felt a bit predictable.'),
(37, 2, 7, 3, 'The book had potential, but it didn’t quite meet my expectations.'),
(38, 2, 8, 5, 'Such a gripping story, couldn’t put it down! Great twist in the end.'),
(39, 2, 9, 4, 'A beautifully told story about the power of education and resilience.'),
(40, 2, 10, 5, 'A motivational read that inspires greatness in any field.'),
(41, 2, 11, 5, 'A powerful and insightful story about history, culture, and identity.'),
(42, 2, 12, 4, 'A classic that everyone should read at least once, though it can be heavy.'),
(43, 2, 13, 3, 'An enjoyable story, but it lacked the depth I was hoping for.'),
(44, 2, 14, 5, 'An unforgettable read that I’ll never forget, such an emotional journey.'),
(45, 2, 15, 4, 'A good story, but the pacing was inconsistent for my taste.'),
(46, 3, 1, 5, 'A beautifully written and emotional book, would recommend it to everyone!'),
(47, 3, 2, 4, 'A heartbreaking but beautiful tale, the themes are quite powerful.'),
(48, 3, 3, 5, 'An amazing political memoir, a must-read for anyone interested in leadership.'),
(49, 3, 4, 3, 'It had an interesting premise but didn’t quite live up to the hype.'),
(50, 3, 5, 4, 'An insightful look at the evolution of networks and their influence on society.'),
(51, 3, 6, 5, 'A heartwarming and inspiring tale about overcoming adversity.'),
(52, 3, 7, 4, 'The plot was intriguing, though it felt like it was dragging in places.'),
(53, 3, 8, 3, 'The concept was intriguing, but it lacked the depth I was looking for.'),
(54, 3, 9, 4, 'A great read about the value of education and determination.'),
(55, 3, 10, 5, 'A book that truly opened my eyes to the ways we define success.'),
(56, 3, 11, 5, 'An incredible story that blends history with personal identity.'),
(57, 3, 12, 4, 'A classic that’s worth reading, though the length can be daunting.'),
(58, 3, 13, 5, 'A beautifully crafted story that perfectly blends love and reason.'),
(59, 3, 14, 4, 'A deeply emotional and moving story, though a bit predictable.'),
(60, 3, 15, 3, 'A solid read, but I didn’t find it as compelling as I expected.'),
(61, 4, 1, 5, 'A heartwarming story that left a lasting impact on me.'),
(62, 4, 2, 3, 'Interesting, but I felt it dragged a little towards the middle.'),
(63, 4, 3, 4, 'A fascinating look at leadership in politics, insightful read.'),
(64, 4, 4, 3, 'Good idea, but could have been more engaging.'),
(65, 4, 5, 5, 'Highly recommended for anyone curious about networks in our world.'),
(66, 4, 6, 4, 'A moving and emotional story about family.'),
(67, 4, 7, 2, 'Not a fan of the slow pacing in this one.'),
(68, 4, 8, 4, 'The ending surprised me, definitely worth reading.'),
(69, 4, 9, 5, 'Such an inspiring story, this one really spoke to me.'),
(70, 4, 10, 4, 'Great insights into success, although some parts felt repetitive.'),
(71, 4, 11, 4, 'A poignant tale of identity, could have used more depth.'),
(72, 4, 12, 5, 'A masterpiece, I loved every part of it.'),
(73, 4, 13, 3, 'It was good, but the pacing didn’t quite match the plot.'),
(74, 4, 14, 5, 'Incredibly emotional, a beautiful story about resilience.'),
(75, 4, 15, 4, 'Enjoyable, but the ending felt rushed for me.'),
(76, 5, 1, 5, 'This book resonated with me deeply, such a powerful story.'),
(77, 5, 2, 4, 'Well-written, though the pacing felt slow at times.'),
(78, 5, 3, 4, 'An informative and inspiring read about political change.'),
(79, 5, 4, 5, 'Loved it! Kept me hooked the whole time.'),
(80, 5, 5, 5, 'A great read for anyone interested in the impact of networks.'),
(81, 5, 6, 3, 'It was okay, but I expected more emotional depth.'),
(82, 5, 7, 4, 'A unique concept with great potential, though a bit slow.'),
(83, 5, 8, 5, 'Such a thrilling book, I could not put it down!'),
(84, 5, 9, 4, 'A motivational and encouraging story about overcoming challenges.'),
(85, 5, 10, 5, 'This book truly changed my perspective on success.'),
(86, 5, 11, 5, 'A gripping and emotional story that stays with you.'),
(87, 5, 12, 4, 'A classic for a reason, though it could have been a little shorter.'),
(88, 5, 13, 3, 'Good story, but I expected a little more depth from the characters.'),
(89, 5, 14, 5, 'This book took me on an emotional rollercoaster, absolutely loved it!'),
(90, 5, 15, 2, 'It had potential but felt like it didn’t reach its peak.'),
(91, 6, 1, 3, 'The writing was great, but the pacing didn’t captivate me.'),
(92, 6, 2, 5, 'An emotionally powerful book, highly recommend.'),
(93, 6, 3, 5, 'This book really helped me understand the complexity of leadership.'),
(94, 6, 4, 4, 'The book was engaging, but a bit predictable at times.'),
(95, 6, 5, 5, 'I loved the deep dive into networks and information sharing.'),
(96, 6, 6, 4, 'A heartwarming and touching family story.'),
(97, 6, 7, 3, 'The plot didn’t quite grip me, but I liked the idea behind it.'),
(98, 6, 8, 4, 'An interesting twist on mystery and horror, definitely engaging.'),
(99, 6, 9, 5, 'This book changed the way I see education and personal growth.'),
(100, 6, 10, 5, 'An eye-opening read on what makes successful people successful.'),
(102, 6, 12, 5, 'An absolute must-read! A beautiful classic.'),
(103, 6, 13, 4, 'A great mix of romance and reason, though it felt slow in places.'),
(104, 6, 14, 4, 'Beautifully written, but I wanted a more satisfying ending.'),
(105, 6, 15, 5, 'I loved this book! It gave me chills and made me think deeply.'),
(106, 7, 1, 4, 'A great story, though I found parts of it to be a bit slow.'),
(107, 7, 2, 3, 'The book had great potential, but the story felt dragged out.'),
(108, 7, 3, 4, 'Great insights into political leadership, though could be more concise.'),
(109, 7, 4, 5, 'An engaging story that kept me hooked all the way through!'),
(110, 7, 5, 5, 'Excellent read, truly captivating on the topic of networks.'),
(111, 7, 6, 4, 'An emotional rollercoaster, beautifully written.'),
(112, 7, 7, 3, 'The pacing was off, but the concept was interesting enough to finish.'),
(113, 7, 8, 5, 'I couldn’t put this book down! Amazing plot twists and suspense.'),
(114, 7, 9, 4, 'Incredibly motivating and inspiring, would recommend to anyone.'),
(115, 7, 10, 4, 'Great insights into success, though I felt some parts were too long.'),
(116, 7, 11, 4, 'An intriguing exploration of identity, but not without flaws.'),
(117, 7, 12, 5, 'A classic that is timeless, beautiful writing and storytelling.'),
(118, 7, 13, 3, 'The pacing didn’t work for me, but the story was interesting enough.'),
(119, 7, 14, 5, 'This book took me on a journey, incredibly moving and well worth it.'),
(120, 7, 15, 4, 'A good read, though I wasn’t satisfied with the ending.'),
(121, 8, 16, 4, 'The story really touched my heart. I found it slow at times, but the emotional weight of the characters stayed with me long after I finished.'),
(122, 8, 17, 5, 'This book made me feel everything – joy, sorrow, hope, and regret. It’s been a while since I’ve read something so moving.'),
(123, 8, 18, 3, 'I really wanted to love this, but the pacing felt off. The concept is intriguing, though, just didn’t connect with me.'),
(124, 8, 19, 5, 'Mind-blowing! It’s not often that a thriller messes with your mind like this one did. I couldn’t put it down.'),
(125, 8, 20, 4, 'So much heartache in such a dark, twisted story. It kept me hooked, though I had to take breaks—it was that heavy.'),
(126, 8, 21, 5, 'This broke me. It’s so raw and innocent, yet so tragic. My heart ached for these children and their haunting situation.'),
(127, 8, 22, 4, 'It had me on edge for most of it, but I felt like some of the tension got repetitive. Still, a terrifying and immersive read.'),
(128, 8, 23, 5, 'Oscar Wilde’s words just hit differently. The vanity, the tragedy, the downfall—it’s like nothing I’ve ever read before.'),
(129, 8, 24, 4, 'A strange, yet captivating blend of humor and sadness. It was hard to fully get into at first, but eventually, it all clicked.'),
(130, 8, 25, 5, 'This is a book that will change you. The struggles of these women, their strength, their pain—it felt so real.'),
(131, 8, 26, 4, 'It was long, but worth the adventure. There were parts that dragged, but the journey was epic and unforgettable.'),
(132, 8, 27, 5, 'A stunning story of solitude and survival. It reminded me of the power of nature and the strength of the human spirit.'),
(133, 8, 28, 5, 'It felt like a journey of rediscovery for me. A book that makes you look at life and survival through a different lens.'),
(134, 8, 29, 4, 'I loved the idea behind it, but there were moments where I wished it moved faster. Still, a solid read.'),
(135, 8, 30, 5, 'The richness of the characters, the themes of social class, and the struggles—it’s timeless for a reason. A masterpiece.'),
(136, 8, 31, 5, 'This book feels like it was written just for me. It hit so many of my personal values about love and social structures. It’s stayed with me.'),
(137, 9, 32, 5, 'This book was everything I needed—emotional, heartbreaking, and full of hope in the face of injustice. I cried, a lot.'),
(138, 9, 33, 4, 'It’s tough to read, but so worth it. The themes resonate on a deep level, even though the pace was slow in some sections.'),
(139, 9, 34, 5, 'I couldn’t stop thinking about this one. It made me question everything about power, surveillance, and freedom.'),
(140, 9, 35, 3, 'I liked the characters, but the story felt disjointed and didn’t hit me emotionally. I wanted more from it.'),
(141, 9, 36, 4, 'I could really relate to Holden’s sense of alienation. It was a tough read, but I found myself thinking about it long after finishing.'),
(142, 9, 37, 5, 'This book made me uncomfortable in the best way. It forced me to confront ideas I didn’t want to face, and I love it for that.'),
(143, 9, 38, 4, 'I admire the depth of this novel. It’s not a quick read, but it stays with you. The reflections on war and peace were profound.'),
(144, 9, 39, 4, 'Intense and morally complex, but I didn’t feel as emotionally connected to it as I hoped. Still, a great piece of literature.'),
(145, 9, 40, 5, 'I felt for Jane in ways I didn’t expect. Her strength and resilience inspired me more than I anticipated.'),
(146, 9, 41, 5, 'I can’t express how much this book changed me. It’s simple, yet transformative—completely reshaped how I approach my day-to-day life.'),
(147, 9, 42, 4, 'Some of the advice felt a little too much like common sense, but overall, the book is a powerful guide to success.'),
(148, 9, 43, 4, 'This book taught me so much about relationships. It’s not just about business—it’s about connecting with people, truly.'),
(149, 9, 44, 5, 'I feel like a new person after reading this. It’s so empowering and full of energy, I feel like I can do anything now.'),
(150, 9, 45, 4, 'This book made me rethink my relationship with the present. It’s a spiritual journey, though I did get lost in some of the philosophy.'),
(151, 9, 46, 5, 'This is my go-to book when I need motivation. Think and Grow Rich is the foundation of all my ambitions now.'),
(152, 9, 47, 5, 'Such a beautiful reminder of how vulnerability can be a strength. I found myself thinking about this for days after finishing it.'),
(153, 10, 48, 5, 'This book is a game-changer. I feel like I’ve taken control of my life in a way I never thought possible before.'),
(154, 10, 49, 5, 'So simple, yet profound. I feel like I can apply the principles to everything in my life, from work to personal relationships.'),
(155, 10, 50, 5, 'Tony Robbins speaks straight to the soul. I’ve read this twice now, and it’s inspired me to take bold steps in my life.'),
(156, 10, 51, 4, 'I felt every moment of Anne’s life in hiding, though it was hard to read at times. It’s an essential piece of history, though painful.'),
(157, 10, 52, 5, 'Steve Jobs’ life feels like a rollercoaster. Inspirational, frustrating, and brilliant all at once. It’s given me a whole new perspective on innovation.'),
(158, 10, 53, 5, 'I’ve never been so moved by someone’s story before. Nelson Mandela’s strength, forgiveness, and vision left me in awe.'),
(159, 10, 55, 4, 'I enjoyed the parts about Einstein’s personal life, but I struggled with the more technical aspects of the book.'),
(160, 10, 56, 5, 'In Cold Blood was unsettling, but it’s such a powerful read. The way Capote tells the story made me feel like I was living in that town.'),
(161, 10, 57, 5, 'Gone Girl was such a twisty, devious ride. I couldn’t put it down, and when I finished it, I was left stunned for days.'),
(162, 10, 58, 4, 'I was fascinated by the mystery, but I felt like the pacing was a bit off at times. Still, an enjoyable read overall.'),
(163, 10, 59, 5, 'Big Little Lies is the kind of book that pulls you in slowly and then hits you with its emotional depth. I couldn’t put it down.'),
(164, 10, 60, 5, 'I’ve never been more scared of a book in my life. This one haunted me, but I loved it. It was perfectly eerie.'),
(165, 10, 61, 4, 'The science was heavy, but the message about our planet’s future was hard to ignore. An important read, though difficult at times.'),
(166, 10, 62, 5, 'This book changed the way I think about history. It’s so eye-opening and full of fascinating facts I never knew about.'),
(167, 10, 63, 5, 'I loved the exploration of hidden dimensions. It was mind-expanding, even if I didn’t fully understand everything.'),
(168, 10, 64, 4, 'It made me think differently about the world. At times, I felt like I was being lectured, but it’s a powerful read nonetheless.'),
(169, 11, 65, 5, 'I loved every moment of this book. It’s like the universe finally made sense to me, even if just for a little while.'),
(170, 11, 66, 4, 'A beautiful retelling of classic myths. I didn’t expect to feel so connected to the gods, but it was magical.'),
(171, 11, 67, 5, 'This book brought Norse mythology to life in such a vivid way. I felt like I was walking through the myths myself.'),
(172, 11, 68, 4, 'Circe’s journey was so powerful, though at times it felt like I wanted more resolution. Still, a haunting story that I can’t forget.'),
(173, 11, 69, 5, 'I was terrified and mesmerized all at once. The atmosphere was so eerie, and the story was so gripping.'),
(174, 11, 70, 5, 'This is the book that made me fall in love with thrillers. It was dark, unsettling, and kept me on the edge of my seat.'),
(175, 11, 71, 4, 'The beginning was slow for me, but once I got into it, I couldn’t stop reading. It’s a haunting story that stays with you.'),
(176, 11, 72, 5, 'This book made me feel like I was right there, in the middle of the chaos. It’s gripping, emotional, and unforgettable.'),
(177, 11, 73, 4, 'There were moments where I wanted to yell at the characters, but that’s a sign of how well the story pulled me in.'),
(178, 11, 74, 5, 'Beautiful, tragic, and timeless. It stayed with me for days after finishing. The love, the pain—it was all so real.'),
(179, 11, 75, 4, 'I appreciated the complexity of the characters, though the plot got a bit too tangled for me at times. Still, a worthwhile read.'),
(180, 11, 76, 5, 'This is the kind of story that stays with you. The depth of the characters and their struggles felt so real.'),
(181, 11, 77, 5, 'I loved how this book made me rethink what matters. It’s a beautiful, transformative story about hope, love, and second chances.'),
(182, 11, 78, 5, 'I will never forget this book. It’s a beautiful testament to the resilience of the human spirit. I cried, I laughed—it’s everything.'),
(183, 11, 79, 4, 'The ideas were incredible, but I felt like it was too long at times. Still, a thought-provoking exploration of the future.'),
(184, 11, 80, 5, 'I couldn’t stop reading this. It’s so fast-paced and intriguing—it felt like I was in the middle of a movie.'),
(185, 11, 81, 4, 'The science was complex, but I loved how it tackled deep existential questions. It’s thought-provoking, but I had a hard time keeping up.'),
(186, 11, 82, 5, 'The passion in this book is unmatched. I couldn’t put it down. It’s the kind of book that makes you want to chase your dreams, no matter what.'),
(187, 12, 83, 5, 'This is the kind of book you read once, then read again because it’s that rich with meaning and depth.'),
(188, 12, 84, 5, 'It broke me in the best way. This was a rollercoaster of emotions and just a beautifully crafted story.'),
(189, 12, 85, 4, 'It took me a while to get into the rhythm, but once I did, it was an emotional experience.'),
(190, 12, 86, 5, 'This is a book that will stay with me for a long time. I’ve never felt so deeply connected to the characters before.'),
(191, 12, 87, 4, 'I loved the characters, but the pacing felt uneven. Still, a worthwhile read that made me reflect deeply.'),
(192, 13, 1, 5, 'An emotional rollercoaster. This book made me laugh and cry—it really stayed with me.'),
(193, 13, 12, 4, 'A beautiful classic with complex characters. The pacing was a bit slow, but worth it.'),
(194, 13, 19, 3, 'It was okay, but I found myself getting distracted. It didn’t fully engage me.'),
(195, 13, 25, 5, 'One of the most powerful books I’ve ever read. A must-read for everyone, full of heart and depth.'),
(196, 13, 30, 4, 'A gripping and timeless tale of human ambition. The twists are brilliant, though the ending felt rushed.'),
(197, 13, 41, 5, 'Atomic Habits has genuinely changed my life. The concepts are simple, but the results are profound.'),
(198, 13, 55, 3, 'Interesting read, but it wasn’t as engaging as I thought it would be. Still informative though.'),
(199, 13, 65, 4, 'A deep dive into the universe. The science is fascinating, but some parts were a bit too technical for me.'),
(200, 13, 73, 5, 'A chilling, atmospheric horror story. It kept me on edge the entire time.'),
(201, 13, 87, 4, 'An intricate fantasy novel with a rich world. It took me a while to fully get into it, but it was worth the effort.'),
(202, 14, 5, 5, 'Absolutely brilliant. The history and significance of information networks were explained so clearly.'),
(203, 14, 15, 4, 'A very interesting mystery with a strong plot, though it lacked a bit of depth in character development.'),
(204, 14, 37, 4, 'A powerful dystopian novel that really makes you think about the future of society.'),
(205, 14, 48, 5, 'Mindset is a life-changing book. It made me reevaluate my personal growth and goals.'),
(206, 14, 9, 4, 'An inspiring read about overcoming adversity. However, some parts felt a bit too idealistic.'),
(207, 14, 22, 5, 'I couldn’t put this one down. A psychological thriller that messes with your mind in the best way.'),
(208, 14, 33, 4, 'A daunting challenge to read, but the depth of the themes is what made it stand out.'),
(209, 14, 59, 3, 'The story was engaging, but some of the characters felt one-dimensional.'),
(210, 14, 78, 5, 'Such an emotional journey. I loved reading about the transformative experience on the Pacific Crest Trail.'),
(211, 14, 61, 4, 'An eye-opening read about extinction. Some parts were dry, but the subject matter is incredibly important.'),
(212, 15, 8, 4, 'A great psychological drama. It kept me on my toes the whole time, though it wasn’t without its flaws.'),
(213, 15, 34, 5, '1984 was intense and thought-provoking. It made me reflect on the world we live in today.'),
(214, 15, 20, 3, 'An okay read, but I didn’t feel emotionally invested in the characters or plot.'),
(215, 15, 47, 5, 'Daring Greatly is a powerful book. It taught me the importance of vulnerability in our lives.'),
(216, 15, 43, 4, 'How to Win Friends and Influence People is filled with timeless advice, though some of it felt a bit dated.'),
(217, 15, 42, 5, 'This book has truly impacted the way I approach my personal and professional life. A must-read.'),
(218, 15, 63, 4, 'The Elegant Universe opened my eyes to new dimensions of physics, though it was a bit hard to follow at times.'),
(219, 15, 18, 4, 'Johnny Got His Gun is heartbreaking and raw. It’s not easy to read, but its impact is undeniable.'),
(220, 15, 52, 5, 'Steve Jobs is a captivating biography. The way it details his genius and flaws is remarkable.'),
(221, 15, 75, 5, 'A haunting and terrifying story. The Exorcist is one of the scariest books I’ve ever read.'),
(222, 6, 11, 4, 'Overall great book, though some parts could have been more concise.');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password_hash`, `payment_method`, `first_name`, `last_name`, `username`, `role`) VALUES
(1, 'john.doe@example.com', 'hashed_password1', 'Credit Card', 'John', 'Doe', 'johndoe', 'user'),
(2, 'jane.smith@example.com', 'hashed_password2', 'Debit Card', 'Jane', 'Smith', 'jsmith', 'user'),
(3, 'alex.brown@example.com', 'hashed_password3', 'Paypal', 'Alex', 'Brown', 'abrown', 'user'),
(4, 'emma.jones@example.com', 'hashed_password4', 'Grabpay', 'Emma', 'Jones', 'ejones', 'user'),
(5, 'chris.wilson@example.com', 'hashed_password5', 'Credit Card', 'Chris', 'Wilson', 'cwilson', 'user'),
(6, 'test@demo.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', '8', 'Test', 'Account', 'demo', 'user'),
(8, 'test1@demo.com', '$2y$10$yO4jThxNxC30LWBRqKRa6upcWtNYROxtlhCL7QNsJ0YafkR0NM0za', '11', 'Test1', 'Demo', 'test1demo', 'user'),
(9, 'test2@demo.cm', '$2y$10$Sy8DeSEXk0YSboVvHJRU/.DUesao9dA29j8RMeW6/uzu6ZAFJBLCG', 'Credit Card', 'test2', 'demo', 'test', 'user'),
(10, 'michael.zhang@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Michael', 'Zhang', 'mzhang', 'user'),
(11, 'sarah.patel@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Paypal', 'Sarah', 'Patel', 'spatel', 'user'),
(12, 'david.garcia@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Grabpay', 'David', 'Garcia', 'dgarcia', 'user'),
(13, 'lisa.chen@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Debit Card', 'Lisa', 'Chen', 'lchen', 'user'),
(14, 'james.kim@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'James', 'Kim', 'jkim', 'user'),
(15, 'emily.wong@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Paypal', 'Emily', 'Wong', 'ewong', 'user'),
(16, 'robert.singh@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Robert', 'Singh', 'rsingh', 'user'),
(17, 'amanda.lopez@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Grabpay', 'Amanda', 'Lopez', 'alopez', 'user'),
(18, 'kevin.nguyen@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Debit Card', 'Kevin', 'Nguyen', 'knguyen', 'user'),
(19, 'rachel.taylor@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Rachel', 'Taylor', 'rtaylor', 'user'),
(20, 'thomas.anderson@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Paypal', 'Thomas', 'Anderson', 'tanderson', 'user'),
(21, 'nicole.martinez@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Nicole', 'Martinez', 'nmartinez', 'user'),
(22, 'william.lee@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Grabpay', 'William', 'Lee', 'wlee', 'user'),
(23, 'sophia.wang@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Debit Card', 'Sophia', 'Wang', 'swang', 'user'),
(24, 'daniel.brown@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Daniel', 'Brown', 'dbrown', 'user'),
(25, 'olivia.wilson@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Paypal', 'Olivia', 'Wilson', 'owilson', 'user'),
(26, 'ryan.kumar@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Ryan', 'Kumar', 'rkumar', 'user'),
(27, 'jessica.chang@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Grabpay', 'Jessica', 'Chang', 'jchang', 'user'),
(28, 'peter.rodriguez@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Debit Card', 'Peter', 'Rodriguez', 'prodriguez', 'user'),
(29, 'michelle.park@example.com', '$2y$10$QAYWT9JHwWoL.G61vCCF.O2FjmdCrOTKnZaUZoMqqknXMX52LToHe', 'Credit Card', 'Michelle', 'Park', 'mpark', 'user'),
(30, 'test2@demo.com', '$2y$10$SChhevSD9PAiW1XGpo3RteYLCG5q2bfcugVdpxNMJu.ALa48cJinm', NULL, 'Test', 'Demo2', 'demo2', 'user'),
(31, 'admin@libriverse.com', '$2y$10$mr3gauDAOPOREO.W2sP6due.vlOyZqYr6FUWKt8mzuH6w.3zx5Afu', NULL, 'Admin', 'User', 'admin', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `catalog_id` (`catalog_id`);

--
-- Indexes for table `catalog`
--
ALTER TABLE `catalog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pickup_location`
--
ALTER TABLE `pickup_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `catalog_id` (`catalog_id`),
  ADD KEY `pickup_location_id` (`pickup_location_id`),
  ADD KEY `fk_payment_id` (`payment_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `catalog`
--
ALTER TABLE `catalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pickup_location`
--
ALTER TABLE `pickup_location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `bookmark_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `bookmark_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalog` (`id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `fk_payment_id` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`),
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `request_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `request_ibfk_3` FOREIGN KEY (`pickup_location_id`) REFERENCES `pickup_location` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

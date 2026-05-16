-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 01 fév. 2024 à 14:32
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_Sondage`
--

-- --------------------------------------------------------

--
-- Structure de la table `datel`
--

CREATE TABLE `datel` (
  `date_lancement` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `datel`
--

INSERT INTO `datel` (`date_lancement`) VALUES
('0000-00-00');

-- --------------------------------------------------------

--
-- Structure de la table `participant`
--

CREATE TABLE `participant` (
  `IdParticipant` int(11) NOT NULL,
  `Mail` varchar(50) DEFAULT NULL,
  `Mdp` varchar(6) DEFAULT NULL,
  `Genre` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `question`
--

CREATE TABLE `question` (
  `NumQ` int(11) NOT NULL,
  `NumS` int(11) NOT NULL,
  `Contenu` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `question`
--

INSERT INTO `question` (`NumQ`, `NumS`, `Contenu`) VALUES
(1, 1, 'les informations partagées sur les réseaux sociaux sont fiables'),
(2, 1, 'L\'usage des réseaux sociaux par les enfants doit etre sous le controle parental'),
(3, 1, 'les réseaux sociaux deviennent une nécessité pour les citoyens'),
(4, 2, 'les jeux vidéo contribuent au développement de la pensée logique');

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE `reponse` (
  `NumQ` int(11) DEFAULT NULL,
  `NumS` int(11) NOT NULL,
  `IdParticipant` int(11) DEFAULT NULL,
  `Rep` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sondage`
--

CREATE TABLE `sondage` (
  `NumS` int(11) NOT NULL,
  `Theme` varchar(50) DEFAULT NULL,
  `DateDebut` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `sondage`
--

INSERT INTO `sondage` (`NumS`, `Theme`, `DateDebut`) VALUES
(1, 'les réseaux sociaux', '2019-05-01'),
(2, 'les jeux vidéo', '2019-06-01');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user` varchar(10) DEFAULT NULL,
  `passwordi` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user`, `passwordi`) VALUES
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', ''),
('', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `participant`
--
ALTER TABLE `participant`
  ADD PRIMARY KEY (`IdParticipant`);

--
-- Index pour la table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`NumQ`),
  ADD KEY `NumS` (`NumS`);

--
-- Index pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD KEY `NumQ` (`NumQ`),
  ADD KEY `NumS` (`NumS`),
  ADD KEY `IdParticipant` (`IdParticipant`);

--
-- Index pour la table `sondage`
--
ALTER TABLE `sondage`
  ADD PRIMARY KEY (`NumS`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `participant`
--
ALTER TABLE `participant`
  MODIFY `IdParticipant` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `question`
--
ALTER TABLE `question`
  MODIFY `NumS` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `NumS` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sondage`
--
ALTER TABLE `sondage`
  MODIFY `NumS` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`NumS`) REFERENCES `sondage` (`NumS`);

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `reponse_ibfk_1` FOREIGN KEY (`NumQ`) REFERENCES `question` (`NumQ`),
  ADD CONSTRAINT `reponse_ibfk_2` FOREIGN KEY (`NumS`) REFERENCES `sondage` (`NumS`),
  ADD CONSTRAINT `reponse_ibfk_3` FOREIGN KEY (`IdParticipant`) REFERENCES `participant` (`IdParticipant`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
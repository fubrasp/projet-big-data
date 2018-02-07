name := "sparkperrin3questions"

version := "1.0"

scalaVersion := "2.10.4"

libraryDependencies ++= Seq(
  "org.apache.spark" % "spark-core_2.10" % "2.0.0",
  "org.apache.spark" % "spark-sql_2.10" % "2.0.0",
  "org.apache.spark" %% "spark-mllib" % "2.0.0"
)
